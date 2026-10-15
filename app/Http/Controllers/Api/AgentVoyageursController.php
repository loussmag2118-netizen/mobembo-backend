<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AgentVoyageursController extends Controller
{
    /**
     * Retourne la liste des voyageurs à contrôler pour l'agence de l'agent connecté.
     *
     * GET /api/agent/voyageurs
     * Headers : Authorization: Bearer <token>
     *
     * Filtre STRICT par agence_id (étanchéité totale entre agences).
     */
    public function index(Request $request)
    {
        $agent = Auth::user();

        // ============================================================
        // SÉCURITÉ : Vérifier que l'agent a bien une agence
        // ============================================================
        if (!$agent || !$agent->agence_id || !in_array($agent->role, ['Agent', 'Admin'], true)) {
            return response()->json([
                'message' => 'Utilisateur non autorisé ou sans agence assignée.',
            ], 403);
        }

        // ============================================================
        // REQUÊTE FILTRÉE PAR AGENCE + PLANIFICATION EN COURS
        // ============================================================
        $query = Ticket::where('agence_id', $agent->agence_id)
            ->whereIn('statut_paiement', ['Paye'])
            ->with([
                'planificationVoyage.trajet.gareArrivee',
                'planificationVoyage.trajet.gareDepart',
                'planificationVoyage.bus',
                'planificationVoyage.vagueDepart',
                'gareArrivee',
                'agentVendeur',
            ]);

        // Filtre optionnel : ?date=2025-06-15
        if ($request->filled('date')) {
            $query->whereHas('planificationVoyage', function ($q) use ($request) {
                $q->where('date_voyage', $request->date);
            });
        }

        // Filtre optionnel : ?quartier=Tié-Tié
        if ($request->filled('quartier')) {
            $query->where('quartier', $request->quartier);
        }

        // Filtre optionnel : ?ville=Pointe-Noire
        if ($request->filled('ville')) {
            $query->where('ville_arrivee', $request->ville);
        }

        $tickets = $query->orderByDesc('created_at')->get();

        // ============================================================
        // TRANSFORMATION POUR FLUTTER
        // ============================================================
        $voyageurs = $tickets->map(function ($ticket) {
            $voyage       = $ticket->planificationVoyage;
            $trajet       = $voyage->trajet ?? null;
            $gareDepart   = $trajet->gareDepart ?? null;
            $gareArrivee  = $trajet->gareArrivee ?? null;
            $vagueDepart  = $voyage->vagueDepart ?? null;

            // Statut de présence côté mobile :
            //   - null  → En attente (gris)
            //   - true  → Présent (vert)
            //   - false → Absent (rouge)
            $present = null;
            if ($ticket->statut_embarquement === 'Present') {
                $present = true;
            } elseif ($ticket->statut_embarquement === 'Absent') {
                $present = false;
            }

            $nomGareEffectif = $gareDepart->nom_gare ?? ($gareArrivee->nom_gare ?? 'Gare Principale');
            $nomAgentVendeur = $ticket->agentVendeur 
                ? trim(($ticket->agentVendeur->prenom ?? '') . ' ' . ($ticket->agentVendeur->nom ?? ''))
                : 'App Mobile (En ligne)';

            return [
                'id'                => $ticket->id,
                'reference'         => $ticket->reference_ticket,
                'qr_code_token'     => $ticket->qr_code_token,

                // Passager
                'nom'               => $ticket->nom_passager,
                'prenom'            => $ticket->prenom_passager,
                'categorie'         => $ticket->categorie_passager ?? 'Adulte',
                'age'               => $ticket->age_passager,
                'telephone'         => $ticket->telephone_passager,

                // Destination & Gares
                'ville_arrivee'     => $ticket->ville_arrivee,
                'quartier'          => $ticket->quartier,
                'agence_arrivee'    => $gareArrivee->nom_gare ?? null,
                'gare_depart'       => $gareDepart->nom_gare ?? null,
                'nom_gare'          => $nomGareEffectif,
                'gare'              => $nomGareEffectif,

                // Vendeur
                'agent_vendeur'     => $nomAgentVendeur,
                'agent'             => $nomAgentVendeur,

                // Trajet / Voyage
                'nom_trajet'        => $trajet
                    ? (($gareDepart->nom_gare ?? '?') . ' → ' . ($gareArrivee->nom_gare ?? '?'))
                    : 'Trajet inconnu',
                'date_voyage'       => $voyage->date_voyage ?? null,
                'heure_depart'      => $vagueDepart->heure_depart ?? null,
                'numero_bus'        => $ticket->numero_bus,

                // Statut de présence
                'present'           => $present,
                'statut_embarquement' => $ticket->statut_embarquement,

                // Méta
                'date_paiement'     => $ticket->date_paiement,
            ];
        });

        return response()->json([
            'success'    => true,
            'agence_id'  => $agent->agence_id,
            'count'      => $voyageurs->count(),
            'voyageurs'  => $voyageurs,
        ], 200);
    }
}