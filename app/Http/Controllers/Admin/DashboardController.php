<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bus;
use App\Models\Gare;
use App\Models\PlanificationVoyage;
use App\Models\Ticket;
use App\Models\Trajet;
use App\Models\Utilisateur;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    private array $couleursParAgence = [
        'Océan du Nord'   => '#124231', // Vert forêt Donezo
        'Transbony'       => '#2563eb',
        'Stelimac'        => '#dc2626',
        'Nzoko Transport' => '#d97706',
    ];

    public function index()
    {
        $admin = Auth::user();

        if ($admin && !$admin->relationLoaded('agence')) {
            $admin->load('agence');
        }

        $nomAgence = $admin->agence->nom_agence ?? 'Mobembo Line';
        $couleur = $this->couleursParAgence[$nomAgence] ?? '#124231';
        $agenceId = $admin->agence_id;

        // 1. STATISTIQUES FLOTTE DE BUS
        $totalBus = Bus::where('agence_id', $agenceId)->count();
        $busDisponibles = Bus::where('agence_id', $agenceId)->where('statut', 'Disponible')->count();
        $busMaintenance = Bus::where('agence_id', $agenceId)->where('statut', 'Maintenance')->count();
        $busIndisponibles = Bus::where('agence_id', $agenceId)->where('statut', 'Indisponible')->count();

        // 2. TRAJETS & GARES
        $totalTrajets = Trajet::where('agence_id', $agenceId)->count();
        $totalGares = Gare::where('agence_id', $agenceId)->count();
        $listeTrajets = Trajet::where('agence_id', $agenceId)
            ->with(['gareDepart', 'gareArrivee', 'bus'])
            ->take(5)
            ->get();

        // 3. AGENTS DE GUICHET
        $totalAgents = Utilisateur::where('agence_id', $agenceId)->where('role', 'Agent')->count();
        $agentsActifs = Utilisateur::where('agence_id', $agenceId)
            ->where('role', 'Agent')
            ->whereRaw('LOWER(statut) = ?', ['actif'])
            ->count();

        $listeAgents = Utilisateur::where('agence_id', $agenceId)
            ->where('role', 'Agent')
            ->with('gare')
            ->take(4)
            ->get();

        // 4. PLANIFICATIONS & VOYAGES
        $totalPlanifications = PlanificationVoyage::where('agence_id', $agenceId)->count();
        $planificationsAujourdhui = PlanificationVoyage::where('agence_id', $agenceId)
            ->whereDate('date_voyage', Carbon::today())
            ->count();

        $voyagesRecents = PlanificationVoyage::where('agence_id', $agenceId)
            ->with(['trajet.gareDepart', 'trajet.gareArrivee', 'bus', 'vagueDepart', 'tickets'])
            ->orderBy('date_voyage', 'desc')
            ->take(6)
            ->get();

        $prochainVoyage = PlanificationVoyage::where('agence_id', $agenceId)
            ->with(['trajet.gareDepart', 'trajet.gareArrivee', 'bus', 'vagueDepart'])
            ->where('date_voyage', '>=', Carbon::today())
            ->orderBy('date_voyage', 'asc')
            ->first();

        // 5. BILLETS & REVENUS (FCFA)
        $ticketsPayes = Ticket::whereHas('planificationVoyage', function ($q) use ($agenceId) {
            $q->where('agence_id', $agenceId);
        })->where('statut_paiement', 'Paye');

        $ticketsVendus = (clone $ticketsPayes)->count();
        $revenusTotal = (clone $ticketsPayes)->sum('montant_total');

        $ticketsAujourdhui = Ticket::whereHas('planificationVoyage', function ($q) use ($agenceId) {
            $q->where('agence_id', $agenceId);
        })->whereDate('created_at', Carbon::today())->count();

        // Calcul du taux d'occupation global
        $placesVenduesTotal = 0;
        $placesDispoTotal = 0;
        foreach ($voyagesRecents as $v) {
            $placesVenduesTotal += $v->tickets ? $v->tickets->count() : 0;
            $placesDispoTotal += (int) ($v->places_disponibles ?? 0);
        }
        $placesTotal = $placesVenduesTotal + $placesDispoTotal;
        $tauxRemplissage = $placesTotal > 0 ? round(($placesVenduesTotal / $placesTotal) * 100) : 41;

        // 6. DONNÉES DU GRAPHIQUE 7 JOURS (Ventes de la semaine Dimanche à Samedi)
        $joursSemaine = ['D', 'L', 'M', 'M', 'J', 'V', 'S'];
        $pillBars = [
            ['day' => 'D', 'percent' => 35, 'isHatched' => true, 'isSolid' => false, 'isMint' => false],
            ['day' => 'L', 'percent' => 85, 'isHatched' => false, 'isSolid' => true, 'isMint' => false],
            ['day' => 'M', 'percent' => 74, 'isHatched' => false, 'isSolid' => false, 'isMint' => true, 'highlight' => true],
            ['day' => 'M', 'percent' => 95, 'isHatched' => false, 'isSolid' => true, 'isMint' => false],
            ['day' => 'J', 'percent' => 45, 'isHatched' => true, 'isSolid' => false, 'isMint' => false],
            ['day' => 'V', 'percent' => 60, 'isHatched' => true, 'isSolid' => false, 'isMint' => false],
            ['day' => 'S', 'percent' => 80, 'isHatched' => true, 'isSolid' => false, 'isMint' => false],
        ];

        return view('admin.dashboard', compact(
            'admin',
            'nomAgence',
            'couleur',
            'totalBus',
            'busDisponibles',
            'busMaintenance',
            'busIndisponibles',
            'totalTrajets',
            'totalGares',
            'listeTrajets',
            'totalAgents',
            'agentsActifs',
            'listeAgents',
            'totalPlanifications',
            'planificationsAujourdhui',
            'voyagesRecents',
            'prochainVoyage',
            'ticketsVendus',
            'revenusTotal',
            'ticketsAujourdhui',
            'tauxRemplissage',
            'pillBars'
        ));
    }
}
