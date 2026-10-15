<?php

namespace App\Services;

use App\Exceptions\ReservationImpossibleException;
use App\Models\PlanificationVoyage;
use App\Models\Ticket;
use Illuminate\Database\UniqueConstraintViolationException;
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

    /** Prix d'un billet pour une catégorie (« Adulte » ou « Enfant »). */
    public function prixPour(PlanificationVoyage $voyage, string $categorie): float
    {
        if ($categorie === 'Enfant' && $voyage->prix_enfant !== null) {
            return (float) $voyage->prix_enfant;
        }

        return (float) $voyage->prix;
    }

    /** Âge maximum « enfant » de l'agence du départ (12 ans par défaut). */
    public function ageMaxEnfant(PlanificationVoyage $voyage): int
    {
        $voyage->loadMissing('agence');

        return (int) ($voyage->agence?->age_max_enfant ?? 12);
    }

    /**
     * Normalise les passagers : catégorie (Adulte par défaut), âge d'un
     * enfant obligatoire et inférieur ou égal à la limite de l'agence.
     *
     * @return array<int, array{nom: string, prenom: ?string, telephone: ?string, categorie: string, age: ?int}>
     */
    public function normaliserPassagers(PlanificationVoyage $voyage, array $passagers): array
    {
        $ageMax = $this->ageMaxEnfant($voyage);

        return array_map(function (array $p) use ($ageMax) {
            $categorie = ($p['categorie'] ?? 'Adulte') === 'Enfant' ? 'Enfant' : 'Adulte';
            $age = isset($p['age']) && $p['age'] !== '' ? (int) $p['age'] : null;

            if ($categorie === 'Enfant') {
                if ($age === null || $age < 0) {
                    throw new ReservationImpossibleException("Indiquez l'âge de chaque enfant.");
                }
                if ($age > $ageMax) {
                    throw new ReservationImpossibleException(
                        "Le tarif enfant s'applique jusqu'à {$ageMax} ans. Choisissez le tarif adulte pour un passager de {$age} ans."
                    );
                }
            }

            return [
                'nom'       => $p['nom'],
                'prenom'    => $p['prenom'] ?? null,
                'telephone' => $p['telephone'] ?? null,
                'categorie' => $categorie,
                'age'       => $categorie === 'Enfant' ? $age : null,
                'siege'     => isset($p['siege']) && $p['siege'] !== '' ? (int) $p['siege'] : null,
            ];
        }, array_values($passagers));
    }

    /** Montant total attendu pour ces passagers. */
    public function montantAttendu(PlanificationVoyage $voyage, array $passagers): float
    {
        return round(array_sum(array_map(
            fn ($p) => $this->prixPour($voyage, $p['categorie'] ?? 'Adulte'),
            $this->normaliserPassagers($voyage, $passagers)
        )), 2);
    }

    /**
     * Vérifie que le montant envoyé correspond aux prix du départ
     * (adulte / enfant) pour ces passagers.
     */
    public function verifierMontant(PlanificationVoyage $voyage, array $passagers, float $montantRecu): void
    {
        $attendu = $this->montantAttendu($voyage, $passagers);

        if (abs($attendu - round($montantRecu, 2)) > 0.01) {
            throw new ReservationImpossibleException(
                "Le montant envoyé ({$montantRecu} FCFA) ne correspond pas au prix du voyage ({$attendu} FCFA)."
            );
        }
    }

    /**
     * Réserve une place par passager et crée les tickets.
     *
     * @param  array<int, array{nom: string, prenom?: ?string, telephone?: ?string, categorie?: string, age?: ?int}>  $passagers
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

        try {
            return $this->reserverDansTransaction(
                $planificationId, $passagers, $nombre, $modePaiement,
                $referencePaiement, $telephonePaiement, $payeImmediatement, $utilisateurId
            );
        } catch (UniqueConstraintViolationException $e) {
            // Dernier filet : la base refuse deux billets actifs sur un même siège
            if (str_contains($e->getMessage(), 'tickets_siege_unique_par_depart')) {
                throw new ReservationImpossibleException(
                    "Un des sièges choisis vient d'être réservé par un autre voyageur. Choisissez-en un autre.", 409
                );
            }
            throw $e;
        }
    }

    private function reserverDansTransaction(
        int $planificationId, array $passagers, int $nombre, string $modePaiement,
        string $referencePaiement, ?string $telephonePaiement, bool $payeImmediatement, ?int $utilisateurId,
    ): Collection {
        return DB::transaction(function () use (
            $planificationId, $passagers, $nombre, $modePaiement,
            $referencePaiement, $telephonePaiement, $payeImmediatement, $utilisateurId
        ) {
            /** @var PlanificationVoyage|null $voyage */
            $voyage = PlanificationVoyage::whereKey($planificationId)->lockForUpdate()->first();

            if (! $voyage) {
                throw new ReservationImpossibleException('Voyage introuvable.', 404);
            }
            $voyage->loadMissing('agence');
            if ($voyage->agence && $voyage->agence->statut !== 'Actif') {
                throw new ReservationImpossibleException("Cette agence ne vend pas de billets pour le moment.", 403);
            }
            if ($voyage->statut_voyage !== 'Programmation') {
                throw new ReservationImpossibleException("Ce départ n'est plus ouvert à la réservation.");
            }
            if ($voyage->date_voyage && $voyage->date_voyage_carbon?->copy()->endOfDay()->isPast()) {
                throw new ReservationImpossibleException('Ce départ est déjà passé.');
            }
            if ($this->departParti($voyage)) {
                throw new ReservationImpossibleException("Ce bus est déjà parti : l'heure de départ est passée.");
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

            $voyage->loadMissing(['trajet.gareDepart', 'trajet.gareArrivee', 'vagueDepart', 'bus', 'agence']);
            $passagers = $this->attribuerSieges($voyage, $this->normaliserPassagers($voyage, $passagers));
            $gareDepart  = $voyage->trajet?->gareDepart;
            $gareArrivee = $voyage->trajet?->gareArrivee;
            $vague       = $voyage->vagueDepart;
            $maintenant  = now();

            $tickets = collect();
            foreach ($passagers as $passager) {
                $prix = $this->prixPour($voyage, $passager['categorie']);
                $tickets->push(Ticket::create([
                    'planification_voyage_id' => $voyage->id,
                    'agence_id'               => $voyage->agence_id,
                    'utilisateur_id'          => $utilisateurId,

                    'reference_ticket' => $this->nouvelleReferenceTicket($modePaiement),
                    'qr_code_token'    => Str::random(64),

                    'nom_passager'       => $passager['nom'],
                    'prenom_passager'    => $passager['prenom'] ?? '',
                    'telephone_passager' => $passager['telephone'] ?? null,
                    'categorie_passager' => $passager['categorie'],
                    'age_passager'       => $passager['age'],
                    'numero_siege'       => $passager['siege'],

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

    // ==================================================================
    // SIÈGES (agences avec choix du siège)
    // ==================================================================

    public function choixSiegeActif(PlanificationVoyage $voyage): bool
    {
        $voyage->loadMissing('agence');

        return (bool) ($voyage->agence?->choix_siege);
    }

    /**
     * Sièges occupés par un billet payé ou une réservation non expirée.
     *
     * @return array<int, int>
     */
    public function siegesOccupes(PlanificationVoyage $voyage): array
    {
        return Ticket::where('planification_voyage_id', $voyage->id)
            ->whereNotNull('numero_siege')
            ->where(function ($q) {
                $q->where('statut_paiement', 'Paye')
                  ->orWhere(function ($q) {
                      $q->where('statut_paiement', 'En_Attente')
                        ->where(function ($q) {
                            $q->whereNull('expire_le')->orWhere('expire_le', '>=', now());
                        });
                  });
            })
            ->pluck('numero_siege')
            ->map(fn ($n) => (int) $n)
            ->sort()->values()->all();
    }

    /**
     * Plan du bus pour un départ : disposition, nombre de sièges et sièges
     * occupés (sans aucune donnée personnelle).
     */
    public function planSieges(PlanificationVoyage $voyage): array
    {
        $voyage->loadMissing('bus');
        [$gauche, $droite] = $voyage->bus?->disposition ?? [2, 2];

        return [
            'choix_siege'  => $this->choixSiegeActif($voyage),
            'nombre_sieges'=> $this->placesTotales($voyage),
            'disposition'  => ['gauche' => $gauche, 'droite' => $droite],
            'occupes'      => $this->siegesOccupes($voyage),
        ];
    }

    /**
     * Vérifie les sièges demandés et attribue un siège libre aux passagers
     * qui n'en ont pas choisi. Sans effet si l'agence n'a pas le choix du siège.
     */
    private function attribuerSieges(PlanificationVoyage $voyage, array $passagers): array
    {
        if (! $this->choixSiegeActif($voyage)) {
            return array_map(fn ($p) => [...$p, 'siege' => null], $passagers);
        }

        $total    = $this->placesTotales($voyage);
        $occupes  = array_flip($this->siegesOccupes($voyage));
        $demandes = [];

        foreach ($passagers as $p) {
            $n = $p['siege'];
            if ($n === null) {
                continue;
            }
            if ($n < 1 || $n > $total) {
                throw new ReservationImpossibleException("Le siège {$n} n'existe pas dans ce bus.");
            }
            if (isset($demandes[$n])) {
                throw new ReservationImpossibleException("Le siège {$n} est choisi pour deux passagers.");
            }
            if (isset($occupes[$n])) {
                throw new ReservationImpossibleException(
                    "Le siège {$n} vient d'être réservé par un autre voyageur. Choisissez-en un autre.", 409
                );
            }
            $demandes[$n] = true;
        }

        // Sièges libres attribués dans l'ordre aux passagers sans choix
        $libre = 1;
        foreach ($passagers as &$p) {
            if ($p['siege'] !== null) {
                continue;
            }
            while (isset($occupes[$libre]) || isset($demandes[$libre])) {
                $libre++;
            }
            $p['siege'] = $libre;
            $demandes[$libre] = true;
        }
        unset($p);

        return $passagers;
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
                'categorie'        => $t->categorie_passager ?? 'Adulte',
                'age'              => $t->age_passager,
                'siege'            => $t->numero_siege,
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

    /** Le départ du jour a-t-il déjà quitté l'agence (heure de départ passée) ? */
    public function departParti(PlanificationVoyage $voyage): bool
    {
        $voyage->loadMissing('vagueDepart');
        $date = $voyage->date_voyage_carbon;
        $heure = $voyage->vagueDepart?->heure_depart;
        if (! $date || ! $heure) {
            return false;
        }

        return \Illuminate\Support\Carbon::parse($date->toDateString() . ' ' . $heure)->isPast();
    }

    /**
     * Places occupées d'un départ (billets payés et réservations en cours)
     * et plus grand numéro de siège occupé.
     *
     * @return array{places: int, siege_max: int}
     */
    public function occupation(PlanificationVoyage $voyage): array
    {
        $actifs = Ticket::where('planification_voyage_id', $voyage->id)
            ->where(function ($q) {
                $q->where('statut_paiement', 'Paye')
                  ->orWhere(function ($q) {
                      $q->where('statut_paiement', 'En_Attente')
                        ->where(function ($q) {
                            $q->whereNull('expire_le')->orWhere('expire_le', '>=', now());
                        });
                  });
            });

        return [
            'places'    => (int) (clone $actifs)->sum('nombre_places_reservees'),
            'siege_max' => (int) (clone $actifs)->max('numero_siege'),
        ];
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
