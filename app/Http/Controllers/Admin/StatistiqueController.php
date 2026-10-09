<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\Bus;
use App\Models\Trajet;
use App\Models\Utilisateur;
use Illuminate\Support\Facades\Auth;

class StatistiqueController extends Controller
{
    /**
     * Affiche les statistiques globales de l'agence connectée (tickets vendus, revenus, bus, trajets, agents).
     */
    public function index()
    {
        $agenceId = Auth::user()->agence_id;

        // Nombre total de tickets payés pour les trajets de cette agence
        $ticketsVendus = Ticket::whereHas('planificationVoyage.trajet', function ($query) use ($agenceId) {
            $query->where('agence_id', $agenceId);
        })->where('statut_paiement', 'Paye')->count();

        // Chiffre d'affaires total généré par l'agence
        $revenus = Ticket::whereHas('planificationVoyage.trajet', function ($query) use ($agenceId) {
            $query->where('agence_id', $agenceId);
        })->where('statut_paiement', 'Paye')->sum('montant_total');

        // Nombre de bus enregistrés par l'agence
        $nombreBus = Bus::where('agence_id', $agenceId)->count();

        // Nombre de trajets configurés par l'agence
        $nombreTrajets = Trajet::where('agence_id', $agenceId)->count();

        // Nombre d'agents rattachés à cette agence
        $nombreAgents = Utilisateur::where('agence_id', $agenceId)->where('role', 'Agent')->count();

        return view('admin.statistiques.index', compact(
            'ticketsVendus', 
            'revenus', 
            'nombreBus', 
            'nombreTrajets', 
            'nombreAgents'
        ));
    }
}
