<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClasseTarifaire;
use App\Models\Extra;
use App\Models\Vol;
use App\Models\Ville;
use Illuminate\Http\Request;

class VolApiController extends Controller
{
    public function index()
    {
        $vols = Vol::latest()->paginate(20);
        return view('admin.canadian.vols.index', compact('vols'));
    }

    public function create()
    {
        $villes  = Ville::orderBy('nom')->get();
        $classes = ClasseTarifaire::orderBy('ordre')->get();
        $extras  = Extra::orderBy('ordre')->get();

        return view('admin.canadian.vols.create', compact('villes', 'classes', 'extras'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'numero_vol'         => 'required|string|max:255|unique:vols,numero_vol',
            'compagnie'          => 'nullable|string|max:100',
            'avion'              => 'nullable|string|max:100', // ⭐ AJOUTÉ
            'origine'            => 'required|string|max:255',
            'destination'        => 'required|string|max:255',
            'ville_depart'       => 'required|string|max:100',
            'ville_arrivee'      => 'required|string|max:100',
            'ville_depart_iata'  => 'nullable|string|size:3',
            'ville_arrivee_iata' => 'nullable|string|size:3',
            'date_depart'        => 'required|date',
            'heure_depart'       => 'required|date_format:H:i',
            'date_arrivee'       => 'required|date',
            'heure_arrivee'      => 'required|date_format:H:i',
            'prix'               => 'required|numeric|min:0',
            'places_disponibles' => 'required|integer|min:0',
            'statut'             => 'required|in:programme,en_vol,atterri,annule',
            'type_vol'           => 'required|in:direct,escale',
            'inclusions'         => 'nullable|array',
            'escales'            => 'nullable|array',
        ]);

        $validated['date_depart']  = $validated['date_depart'] . ' ' . $validated['heure_depart'] . ':00';
        $validated['date_arrivee'] = $validated['date_arrivee'] . ' ' . $validated['heure_arrivee'] . ':00';
        unset($validated['heure_depart'], $validated['heure_arrivee']);

        $dep = \Carbon\Carbon::parse($validated['date_depart']);
        $arr = \Carbon\Carbon::parse($validated['date_arrivee']);
        $validated['duree_minutes'] = $dep->diffInMinutes($arr);

        Vol::create($validated);

        return redirect()
            ->route('canadian.vols.index')
            ->with('success', 'Vol créé avec succès.');
    }

    public function show($id)
    {
        $vol = Vol::with('reservations')->findOrFail($id);
        return view('admin.canadian.vols.show', compact('vol'));
    }

    public function edit($id)
    {
        $vol     = Vol::findOrFail($id);
        $villes  = Ville::orderBy('nom')->get();
        $classes = ClasseTarifaire::orderBy('ordre')->get();
        $extras  = Extra::orderBy('ordre')->get();

        return view('admin.canadian.vols.edit', compact('vol', 'villes', 'classes', 'extras'));
    }

    public function update(Request $request, $id)
    {
        $vol = Vol::findOrFail($id);

        $validated = $request->validate([
            'numero_vol'         => 'required|string|max:255|unique:vols,numero_vol,' . $id,
            'compagnie'          => 'nullable|string|max:100',
            'avion'              => 'nullable|string|max:100', // ⭐ AJOUTÉ
            'origine'            => 'required|string|max:255',
            'destination'        => 'required|string|max:255',
            'ville_depart'       => 'required|string|max:100',
            'ville_arrivee'      => 'required|string|max:100',
            'ville_depart_iata'  => 'nullable|string|size:3',
            'ville_arrivee_iata' => 'nullable|string|size:3',
            'date_depart'        => 'required|date',
            'heure_depart'       => 'required|date_format:H:i',
            'date_arrivee'       => 'required|date',
            'heure_arrivee'      => 'required|date_format:H:i',
            'prix'               => 'required|numeric|min:0',
            'places_disponibles' => 'required|integer|min:0',
            'statut'             => 'required|in:programme,en_vol,atterri,annule',
            'type_vol'           => 'required|in:direct,escale',
            'inclusions'         => 'nullable|array',
            'escales'            => 'nullable|array',
        ]);

        $validated['date_depart']  = $validated['date_depart'] . ' ' . $validated['heure_depart'] . ':00';
        $validated['date_arrivee'] = $validated['date_arrivee'] . ' ' . $validated['heure_arrivee'] . ':00';
        unset($validated['heure_depart'], $validated['heure_arrivee']);

        $dep = \Carbon\Carbon::parse($validated['date_depart']);
        $arr = \Carbon\Carbon::parse($validated['date_arrivee']);
        $validated['duree_minutes'] = $dep->diffInMinutes($arr);

        $vol->update($validated);

        return redirect()
            ->route('canadian.vols.index')
            ->with('success', 'Vol modifié avec succès.');
    }

    public function destroy($id)
    {
        Vol::findOrFail($id)->delete();
        return redirect()
            ->route('canadian.vols.index')
            ->with('success', 'Vol supprimé.');
    }

    /**
     * ⭐ API Mobile : Recherche de vols (sans authentification).
     * GET /api/vols/search?ville_depart=X&ville_arrivee=Y&date_depart=Z
     */
    public function search(Request $request)
    {
        try {
            $villeDepart  = $request->query('ville_depart',  $request->query('depart', ''));
            $villeArrivee = $request->query('ville_arrivee', $request->query('arrivee', ''));
            $dateDepart   = $request->query('date_depart',   $request->query('date', null));

            $query = Vol::whereIn('statut', ['programme', 'en_vol']);

            if (!empty($villeDepart)) {
                $query->where(function ($q) use ($villeDepart) {
                    $q->where('ville_depart', 'LIKE', "%{$villeDepart}%")
                      ->orWhere('origine', 'LIKE', "%{$villeDepart}%");
                });
            }

            if (!empty($villeArrivee)) {
                $query->where(function ($q) use ($villeArrivee) {
                    $q->where('ville_arrivee', 'LIKE', "%{$villeArrivee}%")
                      ->orWhere('destination', 'LIKE', "%{$villeArrivee}%");
                });
            }

            if (!empty($dateDepart)) {
                $query->whereDate('date_depart', $dateDepart);
            }

            $vols = $query->orderBy('date_depart')->get()->map(fn($v) => $this->formatVolPublic($v))->values();

            return response()->json([
                'success' => true,
                'vols'    => $vols,
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur : ' . $e->getMessage(),
                'vols'    => [],
            ], 200);
        }
    }

    /**
     * ⭐ API Mobile : Liste des villes disponibles.
     * GET /api/vols/villes
     */
    public function villes()
    {
        try {
            $departs  = Vol::distinct()->pluck('ville_depart')->filter();
            $arrivees = Vol::distinct()->pluck('ville_arrivee')->filter();

            $villes = $departs->merge($arrivees)->unique()->sort()->values();

            return response()->json([
                'success' => true,
                'villes'  => $villes,
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'villes'  => [],
            ], 200);
        }
    }

    /**
     * Formatage d'un vol pour l'API publique mobile.
     */
    private function formatVolPublic($vol): array
    {
        $dateDepart  = !empty($vol->date_depart)  ? \Carbon\Carbon::parse($vol->date_depart)  : null;
        $dateArrivee = !empty($vol->date_arrivee) ? \Carbon\Carbon::parse($vol->date_arrivee) : null;

        $duree = '';
        if ($dateDepart && $dateArrivee) {
            $diff   = $dateDepart->diff($dateArrivee);
            $heures = $diff->h + ($diff->d * 24);
            $duree  = sprintf('%dh%02d', $heures, $diff->i);
        }

        return [
            'id'                 => $vol->id,
            'numero_vol'         => $vol->numero_vol ?? '',
            'numeroVol'          => $vol->numero_vol ?? '',
            'ville_depart'       => $vol->ville_depart ?? '',
            'villeDepart'        => $vol->ville_depart ?? ($vol->origine ?? ''),
            'ville_arrivee'      => $vol->ville_arrivee ?? '',
            'villeArrivee'       => $vol->ville_arrivee ?? ($vol->destination ?? ''),
            'origine'            => $vol->origine ?? '',
            'destination'        => $vol->destination ?? '',
            'heure_depart'       => $dateDepart  ? $dateDepart->format('H:i')  : '',
            'heureDepart'        => $dateDepart  ? $dateDepart->format('H:i')  : '',
            'heure_arrivee'      => $dateArrivee ? $dateArrivee->format('H:i') : '',
            'heureArrivee'       => $dateArrivee ? $dateArrivee->format('H:i') : '',
            'date_depart'        => $dateDepart  ? $dateDepart->format('Y-m-d H:i:s')  : null,
            'date_arrivee'       => $dateArrivee ? $dateArrivee->format('Y-m-d H:i:s') : null,
            'duree_formatee'     => $duree,
            'dureeFormatee'      => $duree,
            'prix'               => (float) ($vol->prix ?? 0),
            'prix_base'          => (float) ($vol->prix ?? 0),
            'places_disponibles' => (int) ($vol->places_disponibles ?? 0),
            'statut'             => $vol->statut ?? 'programme',
            'type_vol'           => $vol->type_vol ?? 'direct',
            'compagnie'          => $vol->compagnie ?? 'Canadian Airways',
            'avion'              => $vol->avion ?? null,
        ];
    }
}