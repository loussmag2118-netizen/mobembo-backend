<?php

namespace App\Http\Controllers\Admin\Canadian;

use App\Http\Controllers\Controller;
use App\Models\ClasseTarifaire;
use App\Models\Extra;
use App\Models\Vol;
use App\Models\Ville;
use Carbon\Carbon;
use Illuminate\Http\Request;

class VolController extends Controller
{
    public function index()
    {
        $vols = Vol::latest()->paginate(5)->withQueryString();
        // Chiffres de tous les vols (pas seulement de la page)
        $parStatut = Vol::selectRaw('statut, COUNT(*) AS n')->groupBy('statut')->pluck('n', 'statut');
        return view('admin.canadian.vols.index', compact('vols', 'parStatut'));
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
            'compagnie'          => 'nullable|string|max:100',
            'inclusions'         => 'nullable|array',
            'escales'            => 'nullable|array',
        ]);

        // Fusionner date + heure
        $validated['date_depart']  = $validated['date_depart'] . ' ' . $validated['heure_depart'] . ':00';
        $validated['date_arrivee'] = $validated['date_arrivee'] . ' ' . $validated['heure_arrivee'] . ':00';
        unset($validated['heure_depart'], $validated['heure_arrivee']);

        // Durée en minutes
        $dep = Carbon::parse($validated['date_depart']);
        $arr = Carbon::parse($validated['date_arrivee']);
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
            'compagnie'          => 'nullable|string|max:100',
            'inclusions'         => 'nullable|array',
            'escales'            => 'nullable|array',
        ]);

        $validated['date_depart']  = $validated['date_depart'] . ' ' . $validated['heure_depart'] . ':00';
        $validated['date_arrivee'] = $validated['date_arrivee'] . ' ' . $validated['heure_arrivee'] . ':00';
        unset($validated['heure_depart'], $validated['heure_arrivee']);

        $dep = Carbon::parse($validated['date_depart']);
        $arr = Carbon::parse($validated['date_arrivee']);
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
}