<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\Gare;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminHistoriqueController extends Controller
{
    /**
     * Historique des opérations filtré par agence et par gare (étanchéité).
     *
     * GET /admin/historique
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // ============================================================
        // 1. REQUÊTE DE BASE — FILTRÉE PAR AGENCE ET PAR GARE
        // ============================================================
        $query = Ticket::where('agence_id', $user->agence_id)
            ->with([
                'planificationVoyage.trajet.gareDepart',
                'planificationVoyage.trajet.gareArrivee',
                'planificationVoyage.bus',
                'gareArrivee',
                'agentVendeur',
                'agentControleur',
            ]);

        // 🔒 ISOLATION STRICTE POUR LES AGENTS :
        // Si l'utilisateur est un Agent, il ne voit QUE les tickets de sa gare de rattachement
        if ($user->isAgent() && $user->gare_id) {
            $query->whereHas('planificationVoyage.trajet', function ($q) use ($user) {
                $q->where('gare_depart_id', $user->gare_id);
            });
        } 
        // Si c'est un Admin, il peut filtrer explicitement par une gare spécifique via le sélecteur
        elseif ($request->filled('gare_id')) {
            $query->whereHas('planificationVoyage.trajet', function ($q) use ($request) {
                $q->where('gare_depart_id', $request->gare_id);
            });
        }

        // ============================================================
        // 2. FILTRES OPTIONNELS
        // ============================================================

        // Filtre par ville d'arrivée
        if ($request->filled('ville')) {
            $query->where('ville_arrivee', $request->ville);
        }

        // Filtre par quartier
        if ($request->filled('quartier')) {
            $query->where('quartier', $request->quartier);
        }

        // Filtre par date de paiement
        if ($request->filled('date')) {
            $query->whereDate('date_paiement', $request->date);
        }

        // Filtre par statut d'embarquement
        if ($request->filled('statut')) {
            $query->where('statut_embarquement', $request->statut);
        }

        // Filtre par statut de paiement
        if ($request->filled('statut_paiement')) {
            $query->where('statut_paiement', $request->statut_paiement);
        }

        // Filtre par agent vendeur
        if ($request->filled('agent_id')) {
            $query->where('agent_vendeur_id', $request->agent_id);
        }

        // Filtre par mode de paiement (MTN / Airtel)
        if ($request->filled('mode_paiement')) {
            $query->where('mode_paiement', $request->mode_paiement);
        }

        // Recherche par nom passager ou référence
        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('nom_passager', 'LIKE', "%{$search}%")
                  ->orWhere('prenom_passager', 'LIKE', "%{$search}%")
                  ->orWhere('reference_ticket', 'LIKE', "%{$search}%")
                  ->orWhere('telephone_passager', 'LIKE', "%{$search}%");
            });
        }

        // ============================================================
        // 3. RÉCUPÉRATION PAGINÉE
        // ============================================================
        $operations = $query
            ->orderByDesc('date_paiement')
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        // ============================================================
        // 4. DONNÉES POUR LES FILTRES (listes déroulantes)
        // ============================================================
        $villesDisponibles = Ticket::where('agence_id', $user->agence_id)
            ->whereNotNull('ville_arrivee')
            ->distinct()
            ->orderBy('ville_arrivee')
            ->pluck('ville_arrivee');

        $quartiersDisponibles = Ticket::where('agence_id', $user->agence_id)
            ->whereNotNull('quartier')
            ->distinct()
            ->orderBy('quartier')
            ->pluck('quartier');

        // Si c'est un agent, on limite les agents visibles à sa gare ; si c'est l'admin, toute l'agence
        $agentsQuery = Utilisateur::where('agence_id', $user->agence_id)->where('role', 'Agent');
        if ($user->isAgent() && $user->gare_id) {
            $agentsQuery->where('gare_id', $user->gare_id);
        }
        $agentsDisponibles = $agentsQuery->orderBy('nom')->get();

        // Liste des gares de l'agence (pour le menu déroulant de filtrage de l'Admin)
        $garesDisponibles = Gare::where('agence_id', $user->agence_id)->orderBy('nom_gare')->get();

        // ============================================================
        // 5. STATISTIQUES RAPIDES
        // ============================================================
        $statsQuery = Ticket::where('agence_id', $user->agence_id);
        if ($user->isAgent() && $user->gare_id) {
            $statsQuery->whereHas('planificationVoyage.trajet', function ($q) use ($user) {
                $q->where('gare_depart_id', $user->gare_id);
            });
        } elseif ($request->filled('gare_id')) {
            $statsQuery->whereHas('planificationVoyage.trajet', function ($q) use ($request) {
                $q->where('gare_depart_id', $request->gare_id);
            });
        }

        $stats = [
            'total'    => (clone $statsQuery)->count(),
            'present'  => (clone $statsQuery)->where('statut_embarquement', 'Present')->count(),
            'absent'   => (clone $statsQuery)->where('statut_embarquement', 'Absent')->count(),
            'attente'  => (clone $statsQuery)->where('statut_embarquement', 'En_Attente')->count(),
            'montant'  => (clone $statsQuery)->where('statut_paiement', 'Paye')->sum('montant_total'),
        ];

        // ============================================================
        // 6. RETOUR VUE
        // ============================================================
        return view('admin.historique.index', compact(
            'operations',
            'villesDisponibles',
            'quartiersDisponibles',
            'agentsDisponibles',
            'garesDisponibles',
            'stats'
        ));
    }

    /**
     * Afficher le détail d'un ticket spécifique.
     *
     * GET /admin/historique/{id}
     */
    public function show($id)
    {
        $user = Auth::user();

        $query = Ticket::where('agence_id', $user->agence_id)
            ->with([
                'planificationVoyage.trajet.gareDepart',
                'planificationVoyage.trajet.gareArrivee',
                'planificationVoyage.bus',
                'planificationVoyage.vagueDepart',
                'gareArrivee',
                'agentVendeur',
                'agentControleur',
            ]);

        // Si agent, restreindre l'accès au détail de sa gare uniquement
        if ($user->isAgent() && $user->gare_id) {
            $query->whereHas('planificationVoyage.trajet', function ($q) use ($user) {
                $q->where('gare_depart_id', $user->gare_id);
            });
        }

        $ticket = $query->findOrFail($id);

        return view('admin.historique.show', compact('ticket'));
    }
}
