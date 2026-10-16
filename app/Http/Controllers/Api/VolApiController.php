<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vol;
use App\Support\FormatVol;
use Illuminate\Http\Request;

/**
 * API publique des vols : seuls les vols en vente des compagnies
 * aériennes actives sont visibles.
 */
class VolApiController extends Controller
{
    /** GET /api/vols/search?ville_depart=&ville_arrivee=&date_depart= */
    public function search(Request $request)
    {
        $villeDepart  = trim((string) $request->query('ville_depart', $request->query('depart', '')));
        $villeArrivee = trim((string) $request->query('ville_arrivee', $request->query('arrivee', '')));
        $dateDepart   = $request->query('date_depart', $request->query('date'));

        $query = Vol::enVente()->with('partenaire.logoMedia');

        if ($villeDepart !== '') {
            $query->where(fn ($q) => $q->where('ville_depart', 'LIKE', "%{$villeDepart}%")->orWhere('origine', 'LIKE', "%{$villeDepart}%"));
        }
        if ($villeArrivee !== '') {
            $query->where(fn ($q) => $q->where('ville_arrivee', 'LIKE', "%{$villeArrivee}%")->orWhere('destination', 'LIKE', "%{$villeArrivee}%"));
        }
        if (! empty($dateDepart) && strtotime((string) $dateDepart)) {
            $query->whereDate('date_depart', $dateDepart);
        }

        return response()->json([
            'success' => true,
            'vols'    => $query->orderBy('date_depart')->get()->map(fn ($v) => FormatVol::pourMobile($v))->values(),
        ]);
    }

    /** GET /api/vols/villes : villes desservies par les vols en vente */
    public function villes()
    {
        $vols = Vol::enVente()->get(['ville_depart', 'ville_arrivee']);

        return response()->json([
            'success' => true,
            'villes'  => $vols->pluck('ville_depart')->merge($vols->pluck('ville_arrivee'))->filter()->unique()->sort()->values(),
        ]);
    }
}
