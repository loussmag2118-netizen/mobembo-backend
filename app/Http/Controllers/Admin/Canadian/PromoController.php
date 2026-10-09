<?php

namespace App\Http\Controllers\Admin\Canadian;

use App\Http\Controllers\Controller;
use App\Models\Promo;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PromoController extends Controller
{
    /**
     * Liste des codes promo.
     */
    public function index()
    {
        $promos       = Promo::orderByDesc('created_at')->paginate(15);
        $actifs       = Promo::where('actif', true)->count();
        $expires      = Promo::whereNotNull('date_fin')
                            ->where('date_fin', '<', now())
                            ->count();
        $utilisations = Promo::sum('utilisations');

        return view('admin.canadian.promos.index', compact(
            'promos', 'actifs', 'expires', 'utilisations'
        ));
    }

    /**
     * Formulaire de création.
     */
    public function create()
    {
        return view('admin.canadian.promos.create');
    }

    /**
     * Enregistrer un nouveau code promo.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code'               => 'required|string|max:50|unique:promos,code',
            'type_reduction'     => 'required|in:pourcentage,fixe',
            'valeur'             => 'required|numeric|min:0',
            'date_debut'         => 'nullable|date',
            'date_fin'           => 'nullable|date|after_or_equal:date_debut',
            'limite_utilisation' => 'nullable|integer|min:1',
            'actif'              => 'boolean',
        ], [
            'code.required'                => 'Le code promo est obligatoire.',
            'code.unique'                  => 'Ce code existe déjà.',
            'type_reduction.required'      => 'Le type de réduction est obligatoire.',
            'type_reduction.in'            => 'Le type doit être "pourcentage" ou "fixe".',
            'valeur.required'              => 'La valeur est obligatoire.',
            'valeur.numeric'               => 'La valeur doit être un nombre.',
            'date_fin.after_or_equal'      => 'La date de fin doit être après la date de début.',
        ]);

        // Normaliser le code en majuscules
        $validated['code']  = strtoupper(trim($validated['code']));
        $validated['actif'] = $request->boolean('actif');

        Promo::create($validated);

        return redirect()
            ->route('canadian.promos.index')
            ->with('success', 'Code promo créé avec succès.');
    }

    /**
     * Formulaire d'édition.
     */
    public function edit($id)
    {
        $promo = Promo::findOrFail($id);
        return view('admin.canadian.promos.edit', compact('promo'));
    }

    /**
     * Mise à jour.
     */
    public function update(Request $request, $id)
    {
        $promo = Promo::findOrFail($id);

        $validated = $request->validate([
            'code'               => 'required|string|max:50|unique:promos,code,' . $id,
            'type_reduction'     => 'required|in:pourcentage,fixe',
            'valeur'             => 'required|numeric|min:0',
            'date_debut'         => 'nullable|date',
            'date_fin'           => 'nullable|date|after_or_equal:date_debut',
            'limite_utilisation' => 'nullable|integer|min:1',
            'actif'              => 'boolean',
        ]);

        $validated['code']  = strtoupper(trim($validated['code']));
        $validated['actif'] = $request->boolean('actif');

        $promo->update($validated);

        return redirect()
            ->route('canadian.promos.index')
            ->with('success', 'Code promo modifié avec succès.');
    }

    /**
     * ⭐ Active / Désactive un code promo.
     */
    public function toggle($id)
    {
        $promo = Promo::findOrFail($id);
        $promo->actif = !$promo->actif;
        $promo->save();

        $etat = $promo->actif ? 'activé' : 'désactivé';

        return redirect()
            ->back()
            ->with('success', "Code promo {$promo->code} {$etat}.");
    }

    /**
     * Suppression.
     */
    public function destroy($id)
    {
        $promo = Promo::findOrFail($id);
        $promo->delete();

        return redirect()
            ->route('canadian.promos.index')
            ->with('success', 'Code promo supprimé.');
    }
}