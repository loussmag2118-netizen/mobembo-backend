<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AgentPresenceController extends Controller
{
    /**
     * Mettre à jour la présence d'un passager (scan QR ou clic manuel).
     *
     * POST /api/agent/update-presence
     * Headers : Authorization: Bearer <token>
     *
     * Body :
     *   - ticket_id (int, requis)
     *   - present (bool, requis)
     *      true  → Présent
     *      false → Absent
     */
    public function update(Request $request)
    {
        // ============================================================
        // 1. VALIDATION
        // ============================================================
        $validator = Validator::make($request->all(), [
            'ticket_id' => 'required|integer|exists:tickets,id',
            'present'   => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        $agent = Auth::user();

        if (!$agent || !$agent->agence_id) {
            return response()->json([
                'message' => 'Utilisateur non autorisé ou sans agence assignée.',
            ], 403);
        }

        // ============================================================
        // 2. RÉCUPÉRATION DU TICKET
        // ============================================================
        $ticket = Ticket::find($request->ticket_id);

        if (!$ticket) {
            return response()->json([
                'message' => 'Ticket introuvable.',
            ], 404);
        }

        // ============================================================
        // 3. 🔒 SÉCURITÉ ÉTANCHÉITÉ : Vérifier que le ticket
        //    appartient bien à l'agence de l'agent connecté
        // ============================================================
        if ((int) $ticket->agence_id !== (int) $agent->agence_id) {
            Log::warning('Tentative d\'accès cross-agence', [
                'agent_id'         => $agent->id,
                'agent_agence_id'  => $agent->agence_id,
                'ticket_id'        => $ticket->id,
                'ticket_agence_id' => $ticket->agence_id,
            ]);

            return response()->json([
                'message' => 'Accès refusé : ce ticket n\'appartient pas à votre agence.',
            ], 403);
        }

        // ============================================================
        // 4. MISE À JOUR DU STATUT
        // ============================================================
        $nouveauStatut = $request->present ? 'Present' : 'Absent';

        $ticket->statut_embarquement = $nouveauStatut;
        $ticket->agent_controleur_id = $agent->id;   // traçabilité : qui a scanné
        $ticket->save();

        return response()->json([
            'success'             => true,
            'message'             => $request->present
                ? 'Passager marqué Présent.'
                : 'Passager marqué Absent.',
            'ticket_id'           => $ticket->id,
            'reference_ticket'    => $ticket->reference_ticket,
            'statut_embarquement' => $ticket->statut_embarquement,
            'agent_controleur_id' => $ticket->agent_controleur_id,
            'updated_at'          => $ticket->updated_at,
        ], 200);
    }

    /**
     * Vérifier le statut d'un ticket par sa référence QR (utilisé après un scan).
     *
     * GET /api/agent/ticket/{reference}
     */
    public function showByReference($reference)
    {
        $agent = Auth::user();

        if (!$agent || !$agent->agence_id) {
            return response()->json([
                'message' => 'Utilisateur non autorisé.',
            ], 403);
        }

        $ticket = Ticket::where('reference_ticket', $reference)
            ->where('agence_id', $agent->agence_id)   // 🔒 étanchéité
            ->with([
                'agence',
                'planificationVoyage.trajet.gareDepart',
                'planificationVoyage.trajet.gareArrivee',
                'planificationVoyage.bus',
                'planificationVoyage.vagueDepart',
                'gareArrivee',
                'agentVendeur',
                'agentControleur',
            ])
            ->first();

        if (!$ticket) {
            return response()->json([
                'message' => 'Ticket introuvable pour votre agence.',
            ], 404);
        }

        $voyage      = $ticket->planificationVoyage;
        $trajet      = $voyage->trajet ?? null;
        $gareDepart  = $trajet->gareDepart ?? null;
        $gareArrivee = $ticket->gareArrivee ?? ($trajet->gareArrivee ?? null);
        $vendeur     = $ticket->agentVendeur;
        $controleur  = $ticket->agentControleur;

        return response()->json([
            'success' => true,
            'ticket'  => [
                'id'                  => $ticket->id,
                'reference'           => $ticket->reference_ticket,

                // Passager
                'nom'                 => $ticket->nom_passager,
                'prenom'              => $ticket->prenom_passager,
                'telephone'           => $ticket->telephone_passager,

                // Voyage
                'agence'              => $ticket->agence->nom_agence ?? null,
                'ville_depart'        => $trajet->ville_depart ?? null,
                'gare_depart'         => $gareDepart->nom_gare ?? null,
                'ville_arrivee'       => $ticket->ville_arrivee ?? ($trajet->ville_arrivee ?? null),
                'gare_arrivee'        => $gareArrivee->nom_gare ?? null,
                'quartier'            => $ticket->quartier,
                'nom_trajet'          => $trajet
                    ? (($gareDepart->nom_gare ?? '?') . ' → ' . ($gareArrivee->nom_gare ?? '?'))
                    : null,
                'date_voyage'         => $voyage?->date_voyage?->format('Y-m-d'),
                'heure_depart'        => $voyage->vagueDepart->heure_depart ?? null,
                'numero_bus'          => $ticket->numero_bus ?? ($voyage->bus->immatriculation ?? null),

                // Paiement
                'nombre_places'       => $ticket->nombre_places_reservees,
                'montant_total'       => $ticket->montant_total,
                'statut_paiement'     => $ticket->statut_paiement,
                'mode_paiement'       => $ticket->mode_paiement,
                'date_paiement'       => $ticket->date_paiement?->format('Y-m-d H:i'),
                'date_reservation'    => $ticket->date_reservation?->format('Y-m-d H:i'),

                // Agents & embarquement
                'agent_vendeur'       => $vendeur
                    ? trim(($vendeur->prenom ?? '') . ' ' . ($vendeur->nom ?? ''))
                    : 'App Mobile (En ligne)',
                'agent_controleur'    => $controleur
                    ? trim(($controleur->prenom ?? '') . ' ' . ($controleur->nom ?? ''))
                    : null,
                'statut_embarquement' => $ticket->statut_embarquement,
            ],
        ], 200);
    }
}