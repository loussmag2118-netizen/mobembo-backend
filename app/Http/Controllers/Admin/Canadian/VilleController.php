<?php

namespace App\Http\Controllers\Admin\Canadian;

use App\Http\Controllers\Controller;
use App\Models\Ville;
use Illuminate\Http\Request;

class VilleController extends Controller
{
    public function index()
    {
        $villes = Ville::orderBy('nom')->paginate(5)->withQueryString();
        return view('admin.canadian.villes.index', compact('villes'));
    }

    public function create()
    {
        return view('admin.canadian.villes.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom'       => 'required|string|max:100|unique:villes,nom',
            'code_iata' => 'required|string|size:3|unique:villes,code_iata',
            'pays'      => 'required|string|max:100',
            'drapeau'   => 'nullable|string|max:10',
            'actif'     => 'boolean',
        ]);

        $validated['code_iata'] = strtoupper($validated['code_iata']);

        Ville::create($validated);

        return redirect()
            ->route('canadian.villes.index')
            ->with('success', 'Ville ajoutée avec succès.');
    }

    public function edit($id)
    {
        $ville = Ville::findOrFail($id);
        return view('admin.canadian.villes.edit', compact('ville'));
    }

    public function update(Request $request, $id)
    {
        $ville = Ville::findOrFail($id);

        $validated = $request->validate([
            'nom'       => 'required|string|max:100|unique:villes,nom,' . $id,
            'code_iata' => 'required|string|size:3|unique:villes,code_iata,' . $id,
            'pays'      => 'required|string|max:100',
            'drapeau'   => 'nullable|string|max:10',
            'actif'     => 'boolean',
        ]);

        $validated['code_iata'] = strtoupper($validated['code_iata']);

        $ville->update($validated);

        return redirect()
            ->route('canadian.villes.index')
            ->with('success', 'Ville modifiée avec succès.');
    }

    public function destroy($id)
    {
        Ville::findOrFail($id)->delete();
        return redirect()
            ->route('canadian.villes.index')
            ->with('success', 'Ville supprimée.');
    }
}