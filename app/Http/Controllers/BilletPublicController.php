<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Services\PaiementService;

/**
 * Page publique d'un billet, ouverte en scannant son QR code avec
 * n'importe quel téléphone : logo de l'agence, statut et informations du
 * voyage. Accessible seulement avec le jeton secret du QR (64 caractères).
 * L'embarquement, lui, reste validé par l'application des agents.
 */
class BilletPublicController extends Controller
{
    public function afficher(string $jeton)
    {
        $ticket = Ticket::with(['agence.logoMedia', 'planificationVoyage.trajet.gareDepart', 'planificationVoyage.trajet.gareArrivee', 'planificationVoyage.vagueDepart'])
            ->where('qr_code_token', $jeton)
            ->first();

        if (! $ticket) {
            return response()->view('billets.public', ['ticket' => null], 404);
        }

        $test = PaiementService::estReferenceTest($ticket->reference_paiement);
        $statut = match (true) {
            $ticket->statut_paiement === 'Paye' && $ticket->statut_embarquement === 'Present' => ['Déjà utilisé', 'Ce billet a déjà servi à l\'embarquement.', 'slate'],
            $ticket->statut_paiement === 'Paye' && $test                                      => ['Billet de test', 'Paiement simulé : non valable pour voyager.', 'orange'],
            $ticket->statut_paiement === 'Paye'                                               => ['Billet valable', 'Paiement confirmé.', 'emerald'],
            $ticket->statut_paiement === 'En_Attente'                                         => ['Paiement en attente', 'Ce billet n\'est pas encore payé.', 'amber'],
            $ticket->statut_paiement === 'Rembourse'                                          => ['Billet remboursé', 'Ce billet n\'est plus valable.', 'slate'],
            default                                                                            => ['Billet annulé', 'Ce billet n\'est pas valable.', 'rose'],
        };

        // Nom partiellement masqué : « Junior O. »
        $passager = trim(($ticket->prenom_passager ?: '') . ' ' . mb_substr((string) $ticket->nom_passager, 0, 1) . '.');

        return view('billets.public', compact('ticket', 'statut', 'passager'));
    }
}
