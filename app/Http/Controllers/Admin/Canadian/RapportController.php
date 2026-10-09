<?php

namespace App\Http\Controllers\Admin\Canadian;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vol;
use App\Models\Reservation;
use App\Models\Paiement;
use App\Models\Utilisateur;

class RapportController extends Controller
{
    public function index(Request $request)
    {
        $periode = $request->input('periode', 'mois');

        // ============================================================
        // FILTRES PAR DATE
        // ============================================================
        $dateDebut = $request->input('date_debut');
        $dateFin   = $request->input('date_fin');

        $queryReservations = Reservation::query();
        $queryPaiements    = Paiement::query();

        if ($dateDebut) {
            $queryReservations->whereDate('created_at', '>=', $dateDebut);
            $queryPaiements->whereDate('created_at', '>=', $dateDebut);
        }
        if ($dateFin) {
            $queryReservations->whereDate('created_at', '<=', $dateFin);
            $queryPaiements->whereDate('created_at', '<=', $dateFin);
        }

        // ============================================================
        // KPI PRINCIPAUX
        // ============================================================
        $chiffreAffaires   = (clone $queryPaiements)->where('statut', 'Reussi')->sum('montant') ?? 0;
        $totalReservations = (clone $queryReservations)->count();
        $totalClients      = Utilisateur::where('role', 'Client')->count();

        // ============================================================
        // TAUX DE REMPLISSAGE (SAFE)
        // ============================================================
        $placesTotales = 0;
        $placesVendues = 0;

        try {
            $placesTotales = (int) Vol::sum('places_totales');
        } catch (\Exception $e) {
            $placesTotales = 0;
        }

        try {
            $placesVendues = (int) Reservation::whereIn('statut', ['Confirmee', 'En_Attente'])
                ->sum('nombre_places');
        } catch (\Exception $e) {
            $placesVendues = 0;
        }

        $tauxRemplissage = $placesTotales > 0
            ? ($placesVendues / $placesTotales) * 100
            : 0;

        // ============================================================
        // RÉPARTITION DES RÉSERVATIONS PAR STATUT
        // ============================================================
        $confirmees  = (clone $queryReservations)->where('statut', 'Confirmee')->count();
        $enAttente   = (clone $queryReservations)->where('statut', 'En_Attente')->count();
        $annulees    = (clone $queryReservations)->where('statut', 'Annulee')->count();
        $remboursees = (clone $queryReservations)->where('statut', 'Remboursee')->count();

        // ============================================================
        // TOP 5 DES VOLS LES PLUS RENTABLES (SAFE)
        // ============================================================
        $topVols = collect();

        try {
            $topVols = Vol::withCount('reservations')
                ->withSum('reservations', 'montant_total')
                ->orderByDesc('reservations_sum_montant_total')
                ->take(5)
                ->get()
                ->map(function ($vol) {
                    $vol->revenus = $vol->reservations_sum_montant_total ?? 0;
                    return $vol;
                });
        } catch (\Exception $e) {
            $topVols = collect();
        }

        return view('admin.canadian.rapports.index', compact(
            'periode',
            'chiffreAffaires',
            'totalReservations',
            'totalClients',
            'tauxRemplissage',
            'confirmees',
            'enAttente',
            'annulees',
            'remboursees',
            'topVols'
        ));
    }

    /**
     * Générer un rapport (page dédiée).
     */
    public function generer(Request $request)
    {
        return redirect()->route('canadian.rapports.index', $request->all());
    }

    /**
     * Exporter le rapport global en CSV.
     */
    public function export(Request $request)
    {
        $type = $request->input('type', 'csv');

        if ($type === 'csv') {
            $filename = 'rapport_canadian_' . date('Y-m-d') . '.csv';

            $headers = [
                'Content-Type'        => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"$filename\"",
            ];

            $callback = function () {
                $file = fopen('php://output', 'w');
                fputcsv($file, ['Numéro Vol', 'Départ', 'Arrivée', 'Réservations', 'Revenus (FCFA)']);

                try {
                    $vols = Vol::withCount('reservations')
                        ->withSum('reservations', 'montant_total')
                        ->get();

                    foreach ($vols as $vol) {
                        fputcsv($file, [
                            $vol->numero_vol ?? '—',
                            $vol->aeroport_depart ?? '—',
                            $vol->aeroport_arrivee ?? '—',
                            $vol->reservations_count ?? 0,
                            $vol->reservations_sum_montant_total ?? 0,
                        ]);
                    }
                } catch (\Exception $e) {
                    fputcsv($file, ['Erreur lors de la génération', $e->getMessage()]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        }

        return redirect()->route('canadian.rapports.index')
            ->with('success', 'Rapport exporté avec succès.');
    }
}