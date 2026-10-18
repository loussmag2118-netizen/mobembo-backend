<?php

namespace App\Http\Controllers\Admin\Canadian;

use App\Models\Flotte;
use App\Models\Vol;
use App\Support\Audit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FlotteController extends EspaceAerienController
{
    public const STATUTS = ['Actif' => 'En service', 'Maintenance' => 'En maintenance', 'Inactif' => 'Hors service'];

    public function index()
    {
        $flottes = Flotte::deLaCompagnie($this->compagnieId())
            ->withCount(['vols as vols_a_venir' => fn ($q) => $q->where('date_depart', '>', now())->where('statut', 'programme')])
            ->orderBy('modele')
            ->paginate(5)->withQueryString();

        $parStatut = Flotte::deLaCompagnie($this->compagnieId())->selectRaw('statut, COUNT(*) AS n')->groupBy('statut')->pluck('n', 'statut');
        $sieges = (int) Flotte::deLaCompagnie($this->compagnieId())->where('statut', 'Actif')->sum('capacite_places');

        return view('admin.canadian.flotte.index', compact('flottes', 'parStatut', 'sieges'));
    }

    public function create()
    {
        return view('admin.canadian.flotte.create', ['flotte' => new Flotte(['statut' => 'Actif'])]);
    }

    public function store(Request $request)
    {
        $flotte = Flotte::create($this->valider($request) + [
            'agence_id'    => $this->compagnieId(),
            'nom_appareil' => $this->compagnie()->nom_agence,
        ]);
        Audit::log('flotte.ajoutee', $flotte, ['modele' => $flotte->modele], $this->compagnieId());

        return redirect()->route('canadian.flotte.index')->with('success', 'Avion ajouté à la flotte.');
    }

    public function edit($id)
    {
        return view('admin.canadian.flotte.edit', ['flotte' => $this->appareil($id)]);
    }

    public function update(Request $request, $id)
    {
        $flotte = $this->appareil($id);
        $flotte->update($this->valider($request, $flotte) + ['nom_appareil' => $this->compagnie()->nom_agence]);

        return redirect()->route('canadian.flotte.index')->with('success', 'Avion mis à jour.');
    }

    public function destroy($id)
    {
        $flotte = $this->appareil($id);
        if (Vol::where('flotte_id', $flotte->id)->where('date_depart', '>', now())->where('statut', 'programme')->exists()) {
            return back()->with('error', 'Cet avion est prévu sur des vols à venir : changez d\'abord l\'avion de ces vols, ou passez-le « Hors service ».');
        }
        $flotte->delete();

        return redirect()->route('canadian.flotte.index')->with('success', 'Avion retiré de la flotte.');
    }

    private function appareil($id): Flotte
    {
        return Flotte::deLaCompagnie($this->compagnieId())->findOrFail($id);
    }

    private function valider(Request $request, ?Flotte $flotte = null): array
    {
        $request->merge(['immatriculation' => strtoupper(trim((string) $request->immatriculation))]);

        return $request->validate([
            'modele'          => 'required|string|max:100',
            'immatriculation' => ['required', 'string', 'max:20', 'regex:/^[A-Z0-9]{1,2}-?[A-Z0-9]{2,5}$/', Rule::unique('flottes', 'immatriculation')->ignore($flotte?->id)],
            'capacite_places' => 'required|integer|min:1|max:900',
            'annee'           => 'nullable|integer|min:1950|max:' . (date('Y') + 1),
            'statut'          => ['required', Rule::in(array_keys(self::STATUTS))],
        ], [
            'modele.required'          => 'Indiquez le modèle (ex. Boeing 737-800).',
            'immatriculation.required' => 'Indiquez l\'immatriculation de l\'avion (ex. TN-AJB).',
            'immatriculation.regex'  => 'Immatriculation attendue au format TN-AJB (préfixe pays, tiret, lettres).',
            'immatriculation.unique' => 'Cette immatriculation est déjà enregistrée.',
        ]);
    }
}
