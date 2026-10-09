<?php

namespace App\Http\Controllers\Admin\Canadian;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Flotte;

class FlotteController extends Controller
{
    /**
     * Liste des aéronefs.
     */
    public function index()
    {
        $flottes = Flotte::latest()->get();

        return view('admin.canadian.flotte.index', compact('flottes'));
    }

    /**
     * Formulaire de création.
     */
    public function create()
    {
        return view('admin.canadian.flotte.create');
    }

    /**
     * Enregistrer un aéronef.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'modele'          => 'nullable|string|max:100',
            'capacite_places' => 'required|integer|min:1',
            'annee'           => 'nullable|integer|min:1950|max:' . (date('Y') + 1),
            'statut'          => 'required|in:Actif,Maintenance,Inactif',
        ]);

        // ⭐ Nom d'appareil forcé : jamais saisi par l'utilisateur
        $validated['nom_appareil'] = 'Air Canadian';

        Flotte::create($validated);

        return redirect()
            ->route('canadian.flotte.index')
            ->with('success', 'Appareil ajouté à la flotte avec succès.');
    }

    /**
     * Détails d'un aéronef.
     */
    public function show($id)
    {
        $flotte = Flotte::findOrFail($id);

        return view('admin.canadian.flotte.show', compact('flotte'));
    }

    /**
     * Formulaire de modification.
     */
    public function edit($id)
    {
        $flotte = Flotte::findOrFail($id);

        return view('admin.canadian.flotte.edit', compact('flotte'));
    }

    /**
     * Mettre à jour un aéronef.
     */
    public function update(Request $request, $id)
    {
        $flotte = Flotte::findOrFail($id);

        $validated = $request->validate([
            'modele'          => 'nullable|string|max:100',
            'capacite_places' => 'required|integer|min:1',
            'annee'           => 'nullable|integer|min:1950|max:' . (date('Y') + 1),
            'statut'          => 'required|in:Actif,Maintenance,Inactif',
        ]);

        // ⭐ On garantit que le nom reste "Air Canadian"
        $validated['nom_appareil'] = 'Air Canadian';

        $flotte->update($validated);

        return redirect()
            ->route('canadian.flotte.index')
            ->with('success', 'Appareil mis à jour avec succès.');
    }

    /**
     * Supprimer un aéronef.
     */
    public function destroy($id)
    {
        $flotte = Flotte::findOrFail($id);
        $flotte->delete();

        return redirect()
            ->route('canadian.flotte.index')
            ->with('success', 'Appareil retiré avec succès.');
    }
}