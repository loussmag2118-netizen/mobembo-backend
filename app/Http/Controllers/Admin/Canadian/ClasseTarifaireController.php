<?php

namespace App\Http\Controllers\Admin\Canadian;

use App\Models\ClasseTarifaire;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Classes tarifaires de la compagnie : le prix d'une classe = prix du vol × coefficient.
 */
class ClasseTarifaireController extends EspaceAerienController
{
    public function index()
    {
        $classes = ClasseTarifaire::deLaCompagnie($this->compagnieId())->orderBy('ordre')->paginate(5)->withQueryString();

        return view('admin.canadian.classes.index', compact('classes'));
    }

    public function create()
    {
        return view('admin.canadian.classes.form', ['classe' => new ClasseTarifaire(['multiplicateur' => 1, 'actif' => true, 'ordre' => 0])]);
    }

    public function store(Request $request)
    {
        ClasseTarifaire::create($this->valider($request) + ['agence_id' => $this->compagnieId()]);

        return redirect()->route('canadian.classes.index')->with('success', 'Classe tarifaire créée.');
    }

    public function edit($id)
    {
        return view('admin.canadian.classes.form', ['classe' => $this->classe($id)]);
    }

    public function update(Request $request, $id)
    {
        $classe = $this->classe($id);
        $classe->update($this->valider($request, $classe));

        return redirect()->route('canadian.classes.index')->with('success', 'Classe tarifaire modifiée.');
    }

    public function destroy($id)
    {
        $this->classe($id)->delete();

        return redirect()->route('canadian.classes.index')->with('success', 'Classe tarifaire supprimée.');
    }

    private function classe($id): ClasseTarifaire
    {
        return ClasseTarifaire::deLaCompagnie($this->compagnieId())->findOrFail($id);
    }

    private function valider(Request $request, ?ClasseTarifaire $classe = null): array
    {
        $request->merge(['code' => strtolower(preg_replace('/\s+/', '_', trim((string) $request->code)))]);

        $v = $request->validate([
            'code'           => ['required', 'string', 'max:30', 'regex:/^[a-z0-9_]+$/',
                Rule::unique('classes_tarifaires', 'code')->where('agence_id', $this->compagnieId())->whereNull('deleted_at')->ignore($classe?->id)],
            'nom'            => 'required|string|max:100',
            'multiplicateur' => 'required|numeric|min:0.5|max:10',
            'description'    => 'nullable|string|max:500',
            'ordre'          => 'nullable|integer|min:0|max:99',
        ], [
            'code.regex'  => 'Code en minuscules sans espace (ex. economique, affaires).',
            'code.unique' => 'Ce code existe déjà dans votre compagnie.',
        ]);
        $v['ordre'] = (int) ($v['ordre'] ?? 0);
        $v['actif'] = $request->boolean('actif');

        return $v;
    }
}
