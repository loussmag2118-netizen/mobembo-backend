<?php

namespace App\Http\Controllers\Admin\Canadian;

use App\Http\Controllers\Controller;
use App\Models\ClasseTarifaire;
use Illuminate\Http\Request;

class ClasseTarifaireController extends Controller
{
    public function index()
    {
        $classes = ClasseTarifaire::orderBy('ordre')->paginate(5)->withQueryString();
        return view('admin.canadian.classes.index', compact('classes'));
    }

    public function create()
    {
        return view('admin.canadian.classes.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code'           => 'required|string|max:30|unique:classes_tarifaires,code',
            'nom'            => 'required|string|max:100',
            'multiplicateur' => 'required|numeric|min:0.1|max:10',
            'description'    => 'nullable|string',
            'ordre'          => 'nullable|integer|min:0',
            'actif'          => 'boolean',
        ]);

        ClasseTarifaire::create($validated);

        return redirect()
            ->route('canadian.classes.index')
            ->with('success', 'Classe tarifaire créée avec succès.');
    }

    public function edit($id)
    {
        $classe = ClasseTarifaire::findOrFail($id);
        return view('admin.canadian.classes.edit', compact('classe'));
    }

    public function update(Request $request, $id)
    {
        $classe = ClasseTarifaire::findOrFail($id);

        $validated = $request->validate([
            'code'           => 'required|string|max:30|unique:classes_tarifaires,code,' . $id,
            'nom'            => 'required|string|max:100',
            'multiplicateur' => 'required|numeric|min:0.1|max:10',
            'description'    => 'nullable|string',
            'ordre'          => 'nullable|integer|min:0',
            'actif'          => 'boolean',
        ]);

        $classe->update($validated);

        return redirect()
            ->route('canadian.classes.index')
            ->with('success', 'Classe tarifaire modifiée.');
    }

    public function destroy($id)
    {
        ClasseTarifaire::findOrFail($id)->delete();
        return redirect()
            ->route('canadian.classes.index')
            ->with('success', 'Classe tarifaire supprimée.');
    }
}