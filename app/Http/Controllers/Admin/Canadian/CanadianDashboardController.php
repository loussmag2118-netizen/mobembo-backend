<?php

namespace App\Http\Controllers\Admin\Canadian;

use App\Models\Flotte;
use App\Models\Paiement;
use App\Models\Reservation;
use App\Models\Ville;
use App\Models\Vol;
use App\Services\ReservationVolService;

/**
 * Tableau de bord d'une compagnie aérienne.
 */
class CanadianDashboardController extends EspaceAerienController
{
    public function index(ReservationVolService $reservations)
    {
        $reservations->expirer();
        $agenceId = $this->compagnieId();

        $encaisse = Paiement::deLaCompagnie($agenceId)->where('statut', 'Reussi');
        $stats = [
            'chiffre_jour'  => (float) (clone $encaisse)->whereDate('created_at', today())->sum('montant'),
            'chiffre_mois'  => (float) (clone $encaisse)->where('created_at', '>=', now()->startOfMonth())->sum('montant'),
            'vols_a_venir'  => Vol::deLaCompagnie($agenceId)->where('statut', 'programme')->where('date_depart', '>', now())->count(),
            'en_attente'    => Reservation::deLaCompagnie($agenceId)->where('statut', 'En_Attente')->count(),
            'a_rembourser'  => Reservation::deLaCompagnie($agenceId)->where('statut', 'A_Rembourser')->count(),
            'places_mois'   => (int) Reservation::deLaCompagnie($agenceId)->whereIn('statut', ['Paye', 'Confirmee'])
                ->where('date_paiement', '>=', now()->startOfMonth())->sum('nombre_places'),
        ];

        $prochainsVols = Vol::deLaCompagnie($agenceId)->where('statut', 'programme')->where('date_depart', '>', now())
            ->orderBy('date_depart')->limit(5)->get();
        $dernieres = Reservation::deLaCompagnie($agenceId)->with('vol')->latest()->limit(5)->get();

        // Étapes de démarrage tant que la compagnie n'est pas prête à vendre
        $demarrage = [
            ['Ajouter les aéroports desservis', Ville::where('actif', true)->count() >= 2, route('canadian.villes.index')],
            ['Enregistrer un avion', Flotte::deLaCompagnie($agenceId)->exists(), route('canadian.flotte.create')],
            ['Programmer un premier vol', Vol::deLaCompagnie($agenceId)->exists(), route('canadian.vols.create')],
            ['Renseigner le service client', \App\Models\Parametre::deLaCompagnie($agenceId)->where('cle', 'telephone')->whereNotNull('valeur')->exists(), route('canadian.parametres.index')],
        ];

        return view('admin.canadian.dashboard', [
            'compagnie'     => $this->compagnie(),
            'stats'         => $stats,
            'prochainsVols' => $prochainsVols,
            'dernieres'     => $dernieres,
            'demarrage'     => $demarrage,
        ]);
    }
}
