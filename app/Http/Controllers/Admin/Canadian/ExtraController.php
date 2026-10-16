<?php

namespace App\Http\Controllers\Admin\Canadian;

use App\Models\Extra;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Bagages et options vendus avec le billet (bagage en soute, siège, repas…).
 */
class ExtraController extends EspaceAerienController
{
    public const CATEGORIES = [
        'bagage'    => ['Bagage', 'fa-suitcase'],
        'siege'     => ['Siège', 'fa-chair'],
        'repas'     => ['Repas', 'fa-utensils'],
        'assurance' => ['Assurance', 'fa-shield-heart'],
        'wifi'      => ['Wi-Fi', 'fa-wifi'],
        'autre'     => ['Autre', 'fa-star'],
    ];

    public function index()
    {
        $extras = Extra::deLaCompagnie($this->compagnieId())->orderBy('ordre')->paginate(5)->withQueryString();

        return view('admin.canadian.extras.index', compact('extras'));
    }

    public function create()
    {
        return view('admin.canadian.extras.form', ['extra' => new Extra(['categorie' => 'bagage', 'actif' => true, 'ordre' => 0])]);
    }

    public function store(Request $request)
    {
        Extra::create($this->valider($request) + ['agence_id' => $this->compagnieId()]);

        return redirect()->route('canadian.extras.index')->with('success', 'Option créée.');
    }

    public function edit($id)
    {
        return view('admin.canadian.extras.form', ['extra' => $this->extra($id)]);
    }

    public function update(Request $request, $id)
    {
        $extra = $this->extra($id);
        $extra->update($this->valider($request, $extra));

        return redirect()->route('canadian.extras.index')->with('success', 'Option modifiée.');
    }

    public function destroy($id)
    {
        $this->extra($id)->delete();

        return redirect()->route('canadian.extras.index')->with('success', 'Option supprimée.');
    }

    private function extra($id): Extra
    {
        return Extra::deLaCompagnie($this->compagnieId())->findOrFail($id);
    }

    private function valider(Request $request, ?Extra $extra = null): array
    {
        $request->merge(['code' => strtolower(preg_replace('/\s+/', '_', trim((string) $request->code)))]);

        $v = $request->validate([
            'code'        => ['required', 'string', 'max:50', 'regex:/^[a-z0-9_]+$/',
                Rule::unique('extras', 'code')->where('agence_id', $this->compagnieId())->whereNull('deleted_at')->ignore($extra?->id)],
            'nom'         => 'required|string|max:150',
            'description' => 'nullable|string|max:500',
            'prix'        => 'required|numeric|min:0|max:10000000',
            'categorie'   => ['required', Rule::in(array_keys(self::CATEGORIES))],
            'ordre'       => 'nullable|integer|min:0|max:99',
        ], [
            'code.regex'  => 'Code en minuscules sans espace (ex. bagage_23kg).',
            'code.unique' => 'Ce code existe déjà dans votre compagnie.',
        ]);
        $v['ordre'] = (int) ($v['ordre'] ?? 0);
        $v['icone'] = self::CATEGORIES[$v['categorie']][1];
        $v['actif'] = $request->boolean('actif');

        return $v;
    }
}
