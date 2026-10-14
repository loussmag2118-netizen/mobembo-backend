<?php

namespace App\Services;

use App\Exceptions\ReservationImpossibleException;
use App\Models\PlanificationVoyage;
use App\Models\Ticket;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Réservation des places d'un départ (planification_voyages).
 *
 * Règle unique : places_disponibles = places totales du départ
 *                                   − tickets qui occupent une place.
 * Un ticket occupe une place s'il est « Paye », ou « En_Attente » et pas
 * encore expiré. Les tickets « Echoue » ou « Rembourse » la libèrent.
 *
 * Toutes les écritures se font dans une transaction avec un verrou sur la
 * ligne du départ : deux clients ne peuvent pas obtenir la même place.
 */
class ReservationService
{
    /** Durée de la réservation temporaire en attente de paiement (config/paiement.php). */
    public function dureeReservationMinutes(): int
    {
        return max(1, (int) config('paiement.duree_reservation_minutes', 15));
    }

    /**
     * Vérifie que le montant envoyé correspond au prix du départ.
     */
    public function verifierMontant(PlanificationVoyage $voyage, int $nombrePassagers, float $montantRecu): void
    {
        $attendu = round((float) $voyage->prix * $nombrePassagers, 2);

        if (abs($attendu - round($montantRecu, 2)) > 0.01) {
            throw new ReservationImpossibleException(
                "Le montant envoyé ({$montantRecu} FCFA) ne correspond pas au prix du voyage ({$attendu} FCFA)."
            );
        }
    }

    /**
     * Réserve une place par passager et crée les tickets.
     *
     * @param  array<int, array{nom: string, prenom?: ?string, telephone?: ?string}>  $passagers
     * @param  bool  $payeImmediatement  true = paiement déjà confirmé (simulation hors production)
     * @return Collection<int, Ticket>
     */
    public function reserver(
        int $planificationId,
        array $passagers,
        string $modePaiement,
        string $referencePaiement,
        ?string $telephonePaiement = null,
        bool $payeImmediatement = false,
        ?int $utilisateurId = null,
    ): Collection {
        $nombre = count($passagers);
        if ($nombre < 1) {
            throw new ReservationImpossibleException('Au moins un passager est requis.');
        }

        return DB::transaction(function () use (
            $planificationId, $passagers, $nombre, $modePaiement,
            $referencePaiement, $telephonePaiement, $payeImmediatement, $utilisateurId
        ) {
            /** @var PlanificationVoyage|null $voyage */
            $voyage = PlanificationVoyage::whereKey($planificationId)->lockForUpdate()->first();

            if (! $voyage) {
                throw new ReservationImpossibleException('Voyage introuvable.', 404);
            }
            if ($voyage->statut_voyage !== 'Programmation') {
                throw new ReservationImpossibleException("Ce départ n'est plus ouvert à la réservation.");
            }
            if ($voyage->date_voyage && $voyage->date_voyage_carbon?->copy()->endOfDay()->isPast()) {
                throw new ReservationImpossibleException('Ce départ est déjà passé.');
            }

            // Libère d'abord les réservations expirées de ce départ
            $this->expirerTicketsDuVoyage($voyage);
            $this->recalculerPlaces($voyage);

            if ($voyage->places_disponibles < $nombre) {
                $reste = max(0, (int) $voyage->places_disponibles);
                throw new ReservationImpossibleException(
                    $reste === 0
                        ? 'Ce départ est complet.'
                        : "Il ne reste que {$reste} place(s) sur ce départ.",
                    409
                );
            }

            $voyage->loadMissing(['trajet.gareDepart', 'trajet.gareArrivee', 'vagueDepart', 'bus']);
            $gareDepart  = $voyage->trajet?->gareDepart;
            $gareArrivee = $voyage->trajet?->gareArrivee;
            $vague       = $voyage->vagueDepart;
            $prix        = (float) ($voyage->prix ?? 0);
            $maintenant  = now();

            $tickets = collect();
            foreach ($passagers as $passager) {
                $tickets->push(Ticket::create([
                    'planification_voyage_id' => $voyage->id,
                    'agence_id'               => $voyage->agence_id,
                    'utilisateur_id'          => $utilisateurId,

                    'reference_ticket' => $this->nouvelleReferenceTicket($modePaiement),
                    'qr_code_token'    => Str::random(64),

                    'nom_passager'       => $passager['nom'],
                    'prenom_passager'    => $passager['prenom'] ?? '',
                    'telephone_passager' => $passager['telephone'] ?? null,

                    'ville_depart'    => $gareDepart?->ville ?? $voyage->trajet?->ville_depart,
                    'gare_depart_id'  => $gareDepart?->id,
                    'ville_arrivee'   => $gareArrivee?->ville ?? $voyage->trajet?->ville_arrivee,
                    'quartier'        => $gareArrivee?->quartier ?? $gareArrivee?->ville,
                    'gare_arrivee_id' => $gareArrivee?->id,
                    'nom_trajet'      => ($gareDepart?->nom_gare ?? '?') . ' → ' . ($gareArrivee?->nom_gare ?? '?'),
                    'numero_bus'      => $voyage->bus?->immatriculation,
                    'horaire'         => $vague?->heure_depart,
                    'nom_vague'       => $vague?->nom_vague,

                    'nombre_places_reservees' => 1,
                    'prix'                    => $prix,
                    'montant_total'           => $prix,

                    'statut_paiement'     => $payeImmediatement ? 'Paye' : 'En_Attente',
                    'statut_embarquement' => 'En_Attente',
                    'mode_paiement'       => $modePaiement,
                    'telephone_paiement'  => $telephonePaiement,
                    'reference_paiement'  => $referencePaiement,
                    'date_paiement'       => $payeImmediatement ? $maintenant : null,
                    'date_reservation'    => $maintenant,
                    'expire_le'           => $payeImmediatement ? null : $maintenant->copy()->addMinutes($this->dureeReservationMinutes()),
                ]));
            }

            $this->recalculerPlaces($voyage);

            return $tickets;
        });
    }

    /**
     * Format des tickets renvoyé à l'application mobile (inchangé).
     *
     * @param  Collection<int, Ticket>  $tickets
     */
    public function presenterTickets(Collection $tickets): array
    {
        return $tickets->map(function (Ticket $t) {
            $voyage = $t->planificationVoyage;
            $voyage?->loadMissing(['trajet.gareDepart', 'trajet.gareArrivee']);

            return [
                'id'               => $t->id,
                'reference_ticket' => $t->reference_ticket,
                'reference'        => $t->reference_ticket,
                'qr_code_token'    => $t->qr_code_token,
                'nom'              => $t->nom_passager,
                'prenom'           => $t->prenom_passager,
                'telephone'        => $t->telephone_passager,
                'nom_trajet'       => $t->nom_trajet,
                'gareDepart'       => $voyage?->trajet?->gareDepart?->nom_gare ?? '',
                'gareArrivee'      => $voyage?->trajet?->gareArrivee?->nom_gare ?? '',
                'villeDepart'      => $t->ville_depart ?? '',
                'villeArrivee'     => $t->ville_arrivee ?? '',
                'horaire'          => $t->horaire,
                'date'             => $voyage?->date_voyage_carbon?->format('d/m/Y'),
                'numeroBus'        => $t->numero_bus,
                'prix'             => (string) (float) $t->montant_total,
                'statut_paiement'  => $t->statut_paiement,
                'expire_le'        => $t->expire_le?->toIso8601String(),
                'billet_test'      => PaiementService::estReferenceTest($t->reference_paiement),
            ];
        })->values()->all();
    }

    /**
     * Annule une réservation en attente (ex. échec de l'initiation du
     * paiement) : ses tickets passent « Echoue » et leurs places sont libérées.
     */
    public function liberer(string $referencePaiement): int
    {
        return DB::transaction(function () use ($referencePaiement) {
            $voyageIds = Ticket::where('reference_paiement', $referencePaiement)
                ->where('statut_paiement', 'En_Attente')
                ->pluck('planification_voyage_id')->unique()->filter();

            $total = 0;
            foreach ($voyageIds as $voyageId) {
                $voyage = PlanificationVoyage::whereKey($voyageId)->lockForUpdate()->first();
                $total += Ticket::where('reference_paiement', $referencePaiement)
                    ->where('planification_voyage_id', $voyageId)
                    ->where('statut_paiement', 'En_Attente')
                    ->update(['statut_paiement' => 'Echoue', 'expire_le' => null]);
                if ($voyage) {
                    $this->recalculerPlaces($voyage);
                }
            }

            return $total;
        });
    }

    /**
     * Expire toutes les réservations dont le délai de paiement est dépassé
     * et libère leurs places. Sans effet s'il n'y en a aucune.
     *
     * @return int nombre de tickets expirés
     */
    public function expirerReservations(): int
    {
        $voyageIds = Ticket::where('statut_paiement', 'En_Attente')
            ->whereNotNull('expire_le')
            ->where('expire_le', '<', now())
            ->distinct()
            ->pluck('planification_voyage_id')
            ->filter();

        $total = 0;
        foreach ($voyageIds as $voyageId) {
            $total += DB::transaction(function () use ($voyageId) {
                $voyage = PlanificationVoyage::whereKey($voyageId)->lockForUpdate()->first();
                if (! $voyage) {
                    return 0;
                }
                $n = $this->expirerTicketsDuVoyage($voyage);
                $this->recalculerPlaces($voyage);

                return $n;
            });
        }

        if ($total > 0) {
            Log::info("Réservations expirées : {$total} ticket(s) libéré(s).");
        }

        return $total;
    }

    /**
     * Recalcule places_disponibles à partir des tickets réels.
     * À appeler dans une transaction avec le départ verrouillé.
     */
    public function recalculerPlaces(PlanificationVoyage $voyage): void
    {
        $occupees = Ticket::where('planification_voyage_id', $voyage->id)
            ->where(function ($q) {
                $q->where('statut_paiement', 'Paye')
                  ->orWhere(function ($q) {
                      $q->where('statut_paiement', 'En_Attente')
                        ->where(function ($q) {
                            $q->whereNull('expire_le')->orWhere('expire_le', '>=', now());
                        });
                  });
            })
            ->sum('nombre_places_reservees');

        $places = max(0, $this->placesTotales($voyage) - (int) $occupees);

        if ((int) $voyage->places_disponibles !== $places) {
            $voyage->places_disponibles = $places;
            $voyage->saveQuietly();
        }
    }

    /** Capacité du départ : quota de tickets s'il est fixé, sinon capacité du bus. */
    public function placesTotales(PlanificationVoyage $voyage): int
    {
        if ($voyage->nombre_tickets_total !== null) {
            return (int) $voyage->nombre_tickets_total;
        }
        $voyage->loadMissing('bus');

        return (int) ($voyage->bus?->capacite_totale ?? 0);
    }

    private function expirerTicketsDuVoyage(PlanificationVoyage $voyage): int
    {
        return Ticket::where('planification_voyage_id', $voyage->id)
            ->where('statut_paiement', 'En_Attente')
            ->whereNotNull('expire_le')
            ->where('expire_le', '<', now())
            ->update(['statut_paiement' => 'Echoue']);
    }

    private function nouvelleReferenceTicket(string $mode): string
    {
        do {
            $reference = 'TKT-' . strtoupper($mode) . '-' . strtoupper(Str::random(8));
        } while (Ticket::where('reference_ticket', $reference)->exists());

        return $reference;
    }
}
