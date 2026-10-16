<?php

namespace App\Http\Controllers\Admin\Canadian;

use App\Models\Paiement;
use App\Models\Reservation;
use App\Models\Vol;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Rapports d'activité de la compagnie sur une période (par défaut le mois en cours).
 */
class RapportController extends EspaceAerienController
{
    public function index(Request $request)
    {
        [$du, $au] = $this->periode($request);
        $agenceId = $this->compagnieId();

        $encaisse = Paiement::deLaCompagnie($agenceId)->where('statut', 'Reussi')->whereBetween('created_at', [$du, $au]);
        $reservations = Reservation::deLaCompagnie($agenceId)->whereBetween('created_at', [$du, $au]);

        $parStatut = (clone $reservations)->selectRaw('statut, COUNT(*) AS n')->groupBy('statut')->pluck('n', 'statut');

        // Remplissage des vols partis (ou à partir) dans la période
        $vols = Vol::deLaCompagnie($agenceId)->whereBetween('date_depart', [$du, $au])->where('statut', '!=', 'annule')
            ->withSum(['reservations as places_vendues' => fn ($q) => $q->whereIn('statut', ['Paye', 'Confirmee'])], 'nombre_places')
            ->withSum(['reservations as recette' => fn ($q) => $q->whereIn('statut', ['Paye', 'Confirmee'])], 'montant_total')
            ->get();
        $capacite = (int) $vols->sum(fn ($v) => $v->places_totales ?? $v->places_disponibles);
        $vendues = (int) $vols->sum('places_vendues');

        $parJour = (clone $encaisse)->selectRaw('DATE(created_at) AS jour, SUM(montant) AS total')->groupBy('jour')->orderBy('jour')->pluck('total', 'jour');

        $rapport = [
            'du'            => $du,
            'au'            => $au,
            'chiffre'       => (float) (clone $encaisse)->sum('montant'),
            'paiements'     => (clone $encaisse)->count(),
            'reservations'  => (int) $parStatut->sum(),
            'par_statut'    => $parStatut,
            'vols'          => $vols->count(),
            'remplissage'   => $capacite > 0 ? round($vendues / $capacite * 100) : 0,
            'places'        => $vendues,
            'capacite'      => $capacite,
            'par_jour'      => $parJour,
            'top_vols'      => $vols->sortByDesc('recette')->take(5),
            'clients'       => (clone $reservations)->whereIn('statut', ['Paye', 'Confirmee'])->distinct('canadian_user_id')->count('canadian_user_id'),
        ];

        return view('admin.canadian.rapports.index', compact('rapport'));
    }

    public function generer(Request $request)
    {
        return redirect()->route('canadian.rapports.index', $request->only(['du', 'au']));
    }

    /** Export CSV des vols de la période avec leurs ventes. */
    public function exportPdfExcel(Request $request)
    {
        [$du, $au] = $this->periode($request);
        $vols = Vol::deLaCompagnie($this->compagnieId())->whereBetween('date_depart', [$du, $au])
            ->withSum(['reservations as places_vendues' => fn ($q) => $q->whereIn('statut', ['Paye', 'Confirmee'])], 'nombre_places')
            ->withSum(['reservations as recette' => fn ($q) => $q->whereIn('statut', ['Paye', 'Confirmee'])], 'montant_total')
            ->orderBy('date_depart')->get();

        $nom = 'rapport_' . Str::slug($this->compagnie()->nom_agence) . '_' . $du->format('Ymd') . '_' . $au->format('Ymd') . '.csv';

        return response()->streamDownload(function () use ($vols) {
            $f = fopen('php://output', 'w');
            fwrite($f, "\xEF\xBB\xBF");
            fputcsv($f, ['Vol', 'Départ', 'De', 'Vers', 'Statut', 'Places en vente', 'Places vendues', 'Recette (FCFA)'], ';');
            foreach ($vols as $v) {
                fputcsv($f, [
                    $v->numero_vol, $v->date_depart?->format('d/m/Y H:i'), $v->ville_depart, $v->ville_arrivee, $v->statut,
                    $v->places_totales ?? $v->places_disponibles, (int) $v->places_vendues, (int) $v->recette,
                ], ';');
            }
            fclose($f);
        }, $nom, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** @return array{0: Carbon, 1: Carbon} */
    private function periode(Request $request): array
    {
        $du = strtotime((string) $request->du) ? Carbon::parse($request->du)->startOfDay() : now()->startOfMonth();
        $au = strtotime((string) $request->au) ? Carbon::parse($request->au)->endOfDay() : now()->endOfMonth();

        return $au->lt($du) ? [$au->copy()->startOfDay(), $du->copy()->endOfDay()] : [$du, $au];
    }
}
