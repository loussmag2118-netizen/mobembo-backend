<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Utilisateur;
use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;

class AgentHistoryController extends Controller
{
    /**
     * Affiche l'historique des opérations d'un agent spécifique.
     */
    public function show(Utilisateur $agent)
    {
        $admin = Auth::user();

        // 🔒 Sécurité : l'agent doit appartenir à l'agence de l'admin connecté
        abort_if(
            $agent->agence_id !== $admin->agence_id,
            403,
            'Accès non autorisé à cette agence.'
        );

        // 🔒 Sécurité supplémentaire : Si l'admin connecté est restreint ou si l'on souhaite 
        // s'assurer que l'agent ciblé appartient bien à la gare gérée (si applicable)
        // (Optionnel selon vos règles, mais garantit l'étanchéité totale)

        // Récupérer les tickets où l'agent est VENDEUR ou CONTRÔLEUR
        $operations = Ticket::where('agence_id', $admin->agence_id)  // 🔒 étanchéité agence
            ->where(function ($query) use ($agent) {
                $query->where('agent_vendeur_id', $agent->id)
                      ->orWhere('agent_controleur_id', $agent->id);
            })
            ->with([
                'planificationVoyage.trajet.gareDepart',
                'planificationVoyage.trajet.gareArrivee',
                'gareArrivee',
                'agentVendeur',
                'agentControleur',
            ])
            ->latest()
            ->paginate(5)->withQueryString();

        return view('admin.agents.historique', compact('agent', 'operations'));
    }
}
