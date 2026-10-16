<?php

namespace App\Http\Controllers\Admin\Canadian;

use App\Models\Promo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PromoController extends EspaceAerienController
{
    public function index()
    {
        $promos = Promo::deLaCompagnie($this->compagnieId())->latest()->paginate(5)->withQueryString();

        $base = Promo::deLaCompagnie($this->compagnieId());
        $stats = [
            'actifs'       => (clone $base)->utilisables()->count(),
            'expires'      => (clone $base)->whereNotNull('date_fin')->where('date_fin', '<', today())->count(),
            'utilisations' => (int) (clone $base)->sum('utilisations_count'),
        ];

        return view('admin.canadian.promos.index', compact('promos', 'stats'));
    }

    public function create()
    {
        return view('admin.canadian.promos.create', ['promo' => new Promo(['type' => 'pourcentage', 'actif' => true, 'date_debut' => today()])]);
    }

    public function store(Request $request)
    {
        Promo::create($this->valider($request) + ['agence_id' => $this->compagnieId(), 'utilisations_count' => 0]);

        return redirect()->route('canadian.promos.index')->with('success', 'Code promo créé.');
    }

    public function edit($id)
    {
        return view('admin.canadian.promos.edit', ['promo' => $this->promo($id)]);
    }

    public function update(Request $request, $id)
    {
        $promo = $this->promo($id);
        $promo->update($this->valider($request, $promo));

        return redirect()->route('canadian.promos.index')->with('success', "Code {$promo->code} modifié.");
    }

    public function toggle($id)
    {
        $promo = $this->promo($id);
        $promo->update(['actif' => ! $promo->actif]);

        return back()->with('success', "Code {$promo->code} " . ($promo->actif ? 'activé.' : 'désactivé.'));
    }

    public function destroy($id)
    {
        $this->promo($id)->delete();

        return redirect()->route('canadian.promos.index')->with('success', 'Code promo supprimé.');
    }

    private function promo($id): Promo
    {
        return Promo::deLaCompagnie($this->compagnieId())->findOrFail($id);
    }

    private function valider(Request $request, ?Promo $promo = null): array
    {
        $request->merge(['code' => strtoupper(preg_replace('/\s+/', '', (string) $request->code))]);

        $v = $request->validate([
            'code'            => ['required', 'string', 'min:3', 'max:30', 'regex:/^[A-Z0-9_-]+$/',
                Rule::unique('promos', 'code')->where('agence_id', $this->compagnieId())->whereNull('deleted_at')->ignore($promo?->id)],
            'type'            => 'required|in:pourcentage,fixe',
            'valeur'          => ['required', 'numeric', 'min:1', $request->type === 'pourcentage' ? 'max:90' : 'max:10000000'],
            'date_debut'      => 'required|date',
            'date_fin'        => 'required|date|after_or_equal:date_debut',
            'utilisation_max' => 'nullable|integer|min:1',
        ], [
            'code.regex'              => 'Le code ne contient que des lettres, chiffres, tirets.',
            'code.unique'             => 'Ce code existe déjà dans votre compagnie.',
            'valeur.max'              => $request->type === 'pourcentage' ? 'Une réduction ne peut pas dépasser 90 %.' : 'Montant trop élevé.',
            'date_fin.after_or_equal' => 'La date de fin doit être après la date de début.',
        ]);
        $v['actif'] = $request->boolean('actif');

        return $v;
    }
}
