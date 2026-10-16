<?php

namespace App\Http\Controllers\Admin\Canadian;

use App\Models\Ville;
use App\Models\Vol;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Aéroports : référentiel commun à toutes les compagnies aériennes
 * (un aéroport ajouté par une compagnie est proposé aux autres).
 */
class VilleController extends EspaceAerienController
{
    public function index(Request $request)
    {
        $villes = Ville::when($request->filled('q'), fn ($q) => $q->where(fn ($w) => $w
                ->where('nom', 'like', '%' . $request->q . '%')
                ->orWhere('code_iata', 'like', '%' . $request->q . '%')
                ->orWhere('pays', 'like', '%' . $request->q . '%')))
            ->orderBy('nom')
            ->paginate(5)->withQueryString();

        return view('admin.canadian.villes.index', compact('villes'));
    }

    public function create()
    {
        return view('admin.canadian.villes.form', ['ville' => new Ville(['pays' => 'Congo', 'actif' => true])]);
    }

    public function store(Request $request)
    {
        Ville::create($this->valider($request));

        return redirect()->route('canadian.villes.index')->with('success', 'Aéroport ajouté.');
    }

    public function edit($id)
    {
        return view('admin.canadian.villes.form', ['ville' => Ville::findOrFail($id)]);
    }

    public function update(Request $request, $id)
    {
        $ville = Ville::findOrFail($id);
        $ville->update($this->valider($request, $ville));

        return redirect()->route('canadian.villes.index')->with('success', 'Aéroport modifié.');
    }

    public function destroy($id)
    {
        $ville = Ville::findOrFail($id);
        $utilise = Vol::where('ville_depart_iata', $ville->code_iata)->orWhere('ville_arrivee_iata', $ville->code_iata)->exists();
        if ($utilise) {
            return back()->with('error', "L'aéroport {$ville->code_iata} est utilisé par des vols : désactivez-le plutôt que de le supprimer.");
        }
        $ville->delete();

        return redirect()->route('canadian.villes.index')->with('success', 'Aéroport supprimé.');
    }

    private function valider(Request $request, ?Ville $ville = null): array
    {
        $request->merge(['code_iata' => strtoupper(trim((string) $request->code_iata))]);

        $v = $request->validate([
            'nom'       => ['required', 'string', 'max:100', Rule::unique('villes', 'nom')->ignore($ville?->id)],
            'code_iata' => ['required', 'string', 'size:3', 'regex:/^[A-Z]{3}$/', Rule::unique('villes', 'code_iata')->ignore($ville?->id)],
            'pays'      => 'required|string|max:100',
        ], [
            'code_iata.size'   => 'Le code IATA compte exactement 3 lettres (ex. BZV).',
            'code_iata.regex'  => 'Le code IATA compte exactement 3 lettres (ex. BZV).',
            'code_iata.unique' => 'Ce code IATA existe déjà.',
            'nom.unique'       => 'Cette ville existe déjà.',
        ]);
        $v['actif'] = $request->boolean('actif');

        return $v;
    }
}
