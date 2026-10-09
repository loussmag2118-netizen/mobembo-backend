<?php

namespace App\Http\Controllers\Admin\Canadian;

use App\Http\Controllers\Controller;
use App\Models\Extra;
use Illuminate\Http\Request;

class ExtraController extends Controller
{
    public function index()
    {
        $extras = Extra::orderBy('ordre')->paginate(20);
        return view('admin.canadian.extras.index', compact('extras'));
    }

    public function create()
    {
        return view('admin.canadian.extras.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code'        => 'required|string|max:50|unique:extras,code',
            'nom'         => 'required|string|max:150',
            'description' => 'nullable|string',
            'prix'        => 'required|numeric|min:0',
            'categorie'   => 'required|in:bagage,siege,assurance,repas,wifi,autre',
            'icone'       => 'nullable|string|max:50',
            'ordre'       => 'nullable|integer|min:0',
            'actif'       => 'boolean',
        ]);

        Extra::create($validated);

        return redirect()
            ->route('canadian.extras.index')
            ->with('success', 'Extra créé avec succès.');
    }

    public function edit($id)
    {
        $extra = Extra::findOrFail($id);
        return view('admin.canadian.extras.edit', compact('extra'));
    }

    public function update(Request $request, $id)
    {
        $extra = Extra::findOrFail($id);

        $validated = $request->validate([
            'code'        => 'required|string|max:50|unique:extras,code,' . $id,
            'nom'         => 'required|string|max:150',
            'description' => 'nullable|string',
            'prix'        => 'required|numeric|min:0',
            'categorie'   => 'required|in:bagage,siege,assurance,repas,wifi,autre',
            'icone'       => 'nullable|string|max:50',
            'ordre'       => 'nullable|integer|min:0',
            'actif'       => 'boolean',
        ]);

        $extra->update($validated);

        return redirect()
            ->route('canadian.extras.index')
            ->with('success', 'Extra modifié avec succès.');
    }

    public function destroy($id)
    {
        Extra::findOrFail($id)->delete();
        return redirect()
            ->route('canadian.extras.index')
            ->with('success', 'Extra supprimé.');
    }
}