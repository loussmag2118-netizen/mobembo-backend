<?php

namespace App\Services;

use App\Exceptions\ReservationImpossibleException;
use App\Models\CanadianUser;
use App\Models\Notification;
use App\Models\Paiement;
use App\Models\Reservation;
use App\Models\Vol;
use App\Support\Audit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Réservations de vols.
 *
 * Règles (identiques au bus) :
 *  - les places sont bloquées pendant le délai de paiement, puis rendues ;
 *  - le restant d'un vol se recalcule toujours à partir des réservations
 *    actives (jamais par simple incrément / décrément) ;
 *  - une réservation n'est « Paye » qu'après confirmation de l'opérateur ;
 *    confirmer() est idempotent.
 */
class ReservationVolService
{
    public const DELAI_MINUTES = 15;

    /** Réservations non payées dans le délai : places rendues. */
    public function expirer(): void
    {
        $volIds = Reservation::where('statut', 'En_Attente')
            ->whereNotNull('expire_le')
            ->where('expire_le', '<', now())
            ->pluck('vol_id')->unique()->filter();

        foreach ($volIds as $volId) {
            DB::transaction(function () use ($volId) {
                $vol = Vol::whereKey($volId)->lockForUpdate()->first();
                Reservation::where('vol_id', $volId)
                    ->where('statut', 'En_Attente')
                    ->where('expire_le', '<', now())
                    ->update(['statut' => 'Expiree']);
                if ($vol) {
                    $this->recalculerPlaces($vol);
                }
            });
        }
    }

    /** Places restantes = capacité − places des réservations actives. */
    public function recalculerPlaces(Vol $vol): void
    {
        if ($vol->places_totales === null) {
            return;   // ancien vol sans capacité connue : géré à la main
        }

        $occupees = (int) Reservation::where('vol_id', $vol->id)
            ->whereIn('statut', Reservation::STATUTS_ACTIFS)
            ->sum('nombre_places');

        $vol->forceFill(['places_disponibles' => max(0, $vol->places_totales - $occupees)])->save();
    }

    public function placesOccupees(Vol $vol): int
    {
        return (int) Reservation::where('vol_id', $vol->id)
            ->whereIn('statut', Reservation::STATUTS_ACTIFS)
            ->sum('nombre_places');
    }

    /** Le vol est-il proposé à la vente ? (compagnie active, programmé, pas encore parti) */
    public static function volEnVente(Vol $vol): bool
    {
        return $vol->statut === 'programme'
            && $vol->date_depart && $vol->date_depart->isFuture()
            && $vol->partenaire?->statut === 'Actif';
    }

    /**
     * Bloque les places pendant le délai de paiement.
     *
     * @param  array<int, array>  $passagers
     */
    public function reserver(CanadianUser $client, int $volId, int $nombrePlaces, array $passagers = []): Reservation
    {
        $this->expirer();

        return DB::transaction(function () use ($client, $volId, $nombrePlaces, $passagers) {
            $vol = Vol::with('partenaire')->whereKey($volId)->lockForUpdate()->first();

            if (! $vol || ! self::volEnVente($vol)) {
                throw new ReservationImpossibleException('Ce vol n\'est plus proposé à la réservation.', 409);
            }
            $this->recalculerPlaces($vol);
            if ($vol->places_disponibles < $nombrePlaces) {
                throw new ReservationImpossibleException(
                    $vol->places_disponibles > 0
                        ? "Il ne reste que {$vol->places_disponibles} place(s) sur ce vol."
                        : 'Ce vol est complet.',
                    409
                );
            }

            $reservation = Reservation::create([
                'agence_id'        => $vol->agence_id,
                'canadian_user_id' => $client->id,
                'vol_id'           => $vol->id,
                'reference'        => 'RES-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6)),
                'type_reservation' => 'aller_simple',
                'classe'           => 'economique',
                'nombre_places'    => $nombrePlaces,
                'nom_passager'     => trim(($client->prenom ?? '') . ' ' . ($client->nom ?? '')) ?: $client->name,
                'telephone'        => $client->telephone,
                'contact_telephone'=> $client->telephone,
                'prix_billet'      => $vol->prix,
                'prix_extras'      => 0,
                'montant_total'    => round((float) $vol->prix * $nombrePlaces, 2),
                'statut'           => 'En_Attente',
                'expire_le'        => now()->addMinutes(self::DELAI_MINUTES),
            ]);

            foreach (array_slice($passagers, 0, $nombrePlaces) as $p) {
                if (! is_array($p) || blank($p['nom'] ?? null)) {
                    continue;
                }
                $reservation->passagers()->create([
                    'type'             => in_array(mb_strtolower((string) ($p['type'] ?? '')), ['adulte', 'enfant', 'bebe'], true) ? mb_strtolower($p['type']) : 'adulte',
                    'nom'              => mb_substr((string) $p['nom'], 0, 150),
                    'prenom'           => mb_substr((string) ($p['prenom'] ?? ''), 0, 150),
                    'date_naissance'   => strtotime((string) ($p['date_naissance'] ?? '')) ? $p['date_naissance'] : null,
                    'numero_passeport' => $p['numero_passeport'] ?? null,
                    'nationalite'      => ($p['nationalite'] ?? null) ?: 'Congo',
                    'telephone'        => $p['telephone'] ?? null,
                    'email'            => $p['email'] ?? null,
                ]);
            }

            $this->recalculerPlaces($vol);
            Audit::log('vol.reservation_creee', $reservation, ['vol' => $vol->numero_vol, 'places' => $nombrePlaces], $vol->agence_id);

            return $reservation;
        });
    }

    /** Rend les places d'une réservation (annulation, échec du paiement). */
    public function liberer(Reservation $reservation, string $statut): void
    {
        DB::transaction(function () use ($reservation, $statut) {
            $vol = Vol::whereKey($reservation->vol_id)->lockForUpdate()->first();
            $reservation->update(['statut' => $statut, 'expire_le' => null]);
            if ($vol) {
                $this->recalculerPlaces($vol);
            }
        });
    }

    /**
     * Paiement confirmé par l'opérateur. Idempotent. Une réservation expirée
     * entre-temps est réactivée s'il reste de la place ; sinon elle est
     * signalée « à rembourser ».
     */
    public function confirmer(Reservation $reservation, string $operateur, ?string $transaction = null): void
    {
        DB::transaction(function () use ($reservation, $operateur, $transaction) {
            $vol = Vol::whereKey($reservation->vol_id)->lockForUpdate()->first();
            $reservation = Reservation::whereKey($reservation->id)->lockForUpdate()->first();

            if (in_array($reservation->statut, ['Paye', 'Confirmee', 'A_Rembourser'], true)) {
                return;   // déjà traité
            }

            if ($reservation->statut !== 'En_Attente') {
                $libres = $vol ? ($vol->places_totales ?? 0) - $this->placesOccupees($vol) : 0;
                if (! $vol || $libres < $reservation->nombre_places) {
                    $reservation->update(['statut' => 'A_Rembourser']);
                    Log::critical("Paiement {$reservation->reference_paiement} reçu après expiration, plus de place : remboursement nécessaire.");
                    Audit::log('paiement.a_rembourser', $reservation, [
                        'reference' => $reservation->reference_paiement, 'transaction' => $transaction,
                    ], $reservation->agence_id);

                    return;
                }
            }

            $reservation->update([
                'statut'        => 'Paye',
                'date_paiement' => now(),
                'expire_le'     => null,
                'code_pnr'      => $reservation->code_pnr ?: $this->nouveauPnr(),
            ]);
            if ($vol) {
                $this->recalculerPlaces($vol);
            }

            $client = $reservation->canadianUser;
            Paiement::create([
                'agence_id'             => $reservation->agence_id,
                'reservation_id'        => $reservation->id,
                'reference_transaction' => $transaction ?: $reservation->reference_paiement,
                'nom_client'            => $reservation->nom_passager,
                'telephone_client'      => $client?->telephone ?? $reservation->telephone,
                'methode'               => $operateur,
                'montant'               => $reservation->montant_total,
                'statut'                => 'Reussi',
            ]);

            Notification::create([
                'agence_id'        => $reservation->agence_id,
                'canadian_user_id' => $reservation->canadian_user_id,
                'titre'            => 'Paiement confirmé',
                'message'          => 'Votre réservation ' . $reservation->reference . ' est payée. Code de réservation (PNR) : ' . $reservation->code_pnr . '.',
                'type'             => 'success',
                'lu'               => false,
            ]);

            Audit::log('paiement.confirme', $reservation, [
                'reference' => $reservation->reference_paiement, 'transaction' => $transaction,
            ], $reservation->agence_id);
        });
    }

    private function nouveauPnr(): string
    {
        do {
            $pnr = strtoupper(Str::random(6));
        } while (Reservation::where('code_pnr', $pnr)->exists());

        return $pnr;
    }
}
