<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bus;
use App\Models\PlanificationVoyage;
use App\Models\Ticket;
use App\Models\Trajet;
use App\Models\Utilisateur;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class StatistiqueController extends Controller
{
    /**
     * Affiche les statistiques globales et détaillées de l'agence connectée.
     */
    public function index()
    {
        $agenceId = Auth::user()->agence_id;

        // 1. Chiffres clés globaux
        $ticketsQuery = Ticket::whereHas('planificationVoyage', function ($query) use ($agenceId) {
            $query->where('agence_id', $agenceId);
        });

        $ticketsPayes = (clone $ticketsQuery)->where('statut_paiement', 'Paye');
        $ticketsVendus = $ticketsPayes->count();
        $revenus = (float) $ticketsPayes->sum('montant_total');
        $panierMoyen = $ticketsVendus > 0 ? round($revenus / $ticketsVendus) : 0;

        $nombreBus = Bus::where('agence_id', $agenceId)->count();
        $busDispo = Bus::where('agence_id', $agenceId)->where('statut', 'Disponible')->count();
        $tauxDispoBus = $nombreBus > 0 ? round(($busDispo / $nombreBus) * 100) : 0;

        $nombreTrajets = Trajet::where('agence_id', $agenceId)->count();
        $nombreAgents = Utilisateur::where('agence_id', $agenceId)->where('role', 'Agent')->count();
        $nombrePlanifications = PlanificationVoyage::where('agence_id', $agenceId)->count();

        // 2. Évolution mensuelle sur les 6 derniers mois
        $moisLabels = [];
        $moisRevenus = [];
        $moisTickets = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $moisLabels[] = ucfirst($date->locale('fr')->isoFormat('MMMM Y'));

            $rev = Ticket::whereHas('planificationVoyage', function ($q) use ($agenceId) {
                $q->where('agence_id', $agenceId);
            })->where('statut_paiement', 'Paye')
              ->whereYear('created_at', $date->year)
              ->whereMonth('created_at', $date->month)
              ->sum('montant_total');

            $cnt = Ticket::whereHas('planificationVoyage', function ($q) use ($agenceId) {
                $q->where('agence_id', $agenceId);
            })->whereYear('created_at', $date->year)
              ->whereMonth('created_at', $date->month)
              ->count();

            $moisRevenus[] = (float) $rev;
            $moisTickets[] = $cnt;
        }

        // 3. Top Trajets par volume de vente
        $trajets = Trajet::where('agence_id', $agenceId)
            ->with(['gareDepart', 'gareArrivee'])
            ->get();

        $trajetLabels = [];
        $trajetTickets = [];
        $trajetRevenus = [];

        foreach ($trajets as $trajet) {
            $nom = ($trajet->gareDepart->nom_gare ?? $trajet->ville_depart ?? 'Dép.') . ' → ' . ($trajet->gareArrivee->nom_gare ?? $trajet->ville_arrivee ?? 'Arr.');
            $tCount = Ticket::whereHas('planificationVoyage', function ($q) use ($trajet) {
                $q->where('trajet_id', $trajet->id);
            })->where('statut_paiement', 'Paye')->count();

            $tRev = Ticket::whereHas('planificationVoyage', function ($q) use ($trajet) {
                $q->where('trajet_id', $trajet->id);
            })->where('statut_paiement', 'Paye')->sum('montant_total');

            $trajetLabels[] = $nom;
            $trajetTickets[] = $tCount;
            $trajetRevenus[] = (float) $tRev;
        }

        // 4. Répartition des Modes de Paiement
        $modesPaiement = [
            'MTN MoMo'      => (clone $ticketsQuery)->where('mode_paiement', 'like', '%mtn%')->count(),
            'Airtel Money'  => (clone $ticketsQuery)->where('mode_paiement', 'like', '%airtel%')->count(),
            'Espèces Guichet' => (clone $ticketsQuery)->where(function($q) {
                $q->where('mode_paiement', 'like', '%espece%')
                  ->orWhere('mode_paiement', 'Guichet')
                  ->orWhereNull('mode_paiement');
            })->count(),
        ];

        // 5. Statut d'embarquement
        $statutsEmbarquement = [
            'Présents (Scannés)' => (clone $ticketsQuery)->where('statut_embarquement', 'Present')->count(),
            'En attente'          => (clone $ticketsQuery)->where('statut_embarquement', 'En_attente')->count(),
            'Absents'             => (clone $ticketsQuery)->where('statut_embarquement', 'Absent')->count(),
        ];

        return view('admin.statistiques.index', compact(
            'ticketsVendus',
            'revenus',
            'panierMoyen',
            'nombreBus',
            'tauxDispoBus',
            'nombreTrajets',
            'nombreAgents',
            'nombrePlanifications',
            'moisLabels',
            'moisRevenus',
            'moisTickets',
            'trajetLabels',
            'trajetTickets',
            'trajetRevenus',
            'modesPaiement',
            'statutsEmbarquement'
        ));
    }
}
