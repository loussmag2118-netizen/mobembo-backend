<?php

namespace App\Support;

use App\Models\Ticket;
use App\Services\PaiementService;

/**
 * Règles d'embarquement communes (application des agents et administration).
 */
class Embarquement
{
    /**
     * Raison pour laquelle le billet ne permet pas d'embarquer, ou null.
     *
     * @return array{code: string, message: string}|null
     */
    public static function motifRefus(Ticket $ticket): ?array
    {
        if ($ticket->statut_paiement !== 'Paye') {
            return [
                'code'    => 'NON_PAYE',
                'message' => match ($ticket->statut_paiement) {
                    'En_Attente' => 'Billet non payé (paiement en attente) : embarquement refusé.',
                    'Rembourse'  => 'Billet remboursé : embarquement refusé.',
                    default      => 'Billet non valable (paiement échoué) : embarquement refusé.',
                },
            ];
        }

        // Le billet doit être pour le départ du jour (ou de la veille, pour un
        // bus parti tard le soir) et ce départ ne doit pas être annulé
        $voyage = $ticket->planificationVoyage;
        if ($voyage?->statut_voyage === 'Annulé') {
            return ['code' => 'DEPART_ANNULE', 'message' => 'Ce départ a été annulé : embarquement refusé.'];
        }
        $date = $voyage?->date_voyage_carbon;
        if ($date && ($date->isAfter(today()) || $date->lt(today()->subDay()))) {
            return [
                'code'    => 'AUTRE_DATE',
                'message' => 'Billet valable pour le départ du ' . $date->format('d/m/Y') . ' : embarquement refusé aujourd\'hui.',
            ];
        }

        if (PaiementService::estReferenceTest($ticket->reference_paiement)
            && app()->environment('production') && ! config('paiement.mode_test_production')) {
            return ['code' => 'BILLET_TEST', 'message' => "Billet de TEST : il n'est pas valable pour voyager."];
        }

        return null;
    }
}
