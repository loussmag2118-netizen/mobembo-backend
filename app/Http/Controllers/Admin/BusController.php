<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class BusController extends Controller
{
    /**
     * Liste des bus de l'agence connectée.
     */
    public function index()
    {
        $bus = Bus::where('agence_id', Auth::user()->agence_id)
            ->orderByDesc('id')
            ->paginate(5)->withQueryString();

        // Chiffres de toute la flotte (pas seulement de la page)
        $parStatut = Bus::where('agence_id', Auth::user()->agence_id)
            ->selectRaw('statut, COUNT(*) AS n')->groupBy('statut')->pluck('n', 'statut');

        return view('admin.bus.index', compact('bus', 'parStatut'));
    }

    /**
     * Corbeille des bus.
     */
    public function trashed()
    {
        $bus = Bus::onlyTrashed()
            ->where('agence_id', Auth::user()->agence_id)
            ->orderByDesc('deleted_at')
            ->paginate(5)->withQueryString();

        return view('admin.bus.trashed', compact('bus'));
    }

    /**
     * Formulaire de création.
     */
    public function create()
    {
        return view('admin.bus.create');
    }

    /**
     * Enregistrer un nouveau bus.
     */
    public function store(Request $request)
    {
        $agenceId = Auth::user()->agence_id;

        $request->validate([
            'immatriculation' => [
                'required', 'string', 'max:50',
                Rule::unique('bus', 'immatriculation')->where(function ($q) use ($agenceId) {
                    return $q->where('agence_id', $agenceId);
                }),
            ],
            'modele'          => 'required|string|max:100',
            'capacite_totale' => 'required|integer|min:1|max:200',
            'disposition_sieges' => 'nullable|in:1-1,2-1,2-2,3-2',
            'statut'          => 'required|in:Disponible,Maintenance,Indisponible',
        ], [
            'immatriculation.unique' => 'Cette immatriculation existe déjà pour votre agence.',
            'capacite_totale.min'    => 'La capacité doit être au moins de 1 place.',
            'capacite_totale.max'    => 'La capacité ne peut pas dépasser 200 places.',
        ]);

        Bus::create([
            'agence_id'       => $agenceId,
            'immatriculation' => $request->immatriculation,
            'modele'          => $request->modele,
            'capacite_totale' => $request->capacite_totale,
            'disposition_sieges' => $request->input('disposition_sieges', '2-2'),
            'statut'          => $request->statut ?? 'Disponible',
        ]);

        return redirect()
            ->route('admin.bus.index')
            ->with('success', 'Bus ajouté avec succès.');
    }

    /**
     * Formulaire d'édition.
     */
    public function edit($id)
    {
        $bus = Bus::findOrFail($id);
        $this->authorizeAgence($bus);

        return view('admin.bus.edit', compact('bus'));
    }

    /**
     * Mettre à jour un bus.
     */
    public function update(Request $request, $id)
    {
        $bus = Bus::findOrFail($id);
        $this->authorizeAgence($bus);

        $agenceId = Auth::user()->agence_id;

        $request->validate([
            'immatriculation' => [
                'required', 'string', 'max:50',
                Rule::unique('bus', 'immatriculation')
                    ->where(function ($q) use ($agenceId) {
                        return $q->where('agence_id', $agenceId);
                    })
                    ->ignore($bus->id),
            ],
            'modele'          => 'required|string|max:100',
            'capacite_totale' => 'required|integer|min:1|max:200',
            'disposition_sieges' => 'nullable|in:1-1,2-1,2-2,3-2',
            'statut'          => 'required|in:Disponible,Maintenance,Indisponible',
        ], [
            'immatriculation.unique' => 'Cette immatriculation existe déjà pour votre agence.',
        ]);

        $bus->update([
            'immatriculation' => $request->immatriculation,
            'modele'          => $request->modele,
            'capacite_totale' => $request->capacite_totale,
            'disposition_sieges' => $request->input('disposition_sieges', '2-2'),
            'statut'          => $request->statut,
        ]);

        return redirect()
            ->route('admin.bus.index')
            ->with('success', 'Bus mis à jour avec succès.');
    }

    /**
     * Soft delete → corbeille.
     */
    public function destroy($id)
    {
        $bus = Bus::findOrFail($id);
        $this->authorizeAgence($bus);

        $bus->delete();

        return redirect()
            ->route('admin.bus.index')
            ->with('success', 'Bus envoyé dans la corbeille.');
    }

    /**
     * Restaurer un bus depuis la corbeille.
     */
    public function restore($id)
    {
        $bus = Bus::onlyTrashed()->findOrFail($id);
        $this->authorizeAgence($bus);

        $bus->restore();

        return redirect()
            ->route('admin.bus.trashed')
            ->with('success', 'Bus restauré avec succès.');
    }

    /**
     * Suppression définitive.
     */
    public function forceDelete($id)
    {
        $bus = Bus::onlyTrashed()->findOrFail($id);
        $this->authorizeAgence($bus);

        $bus->forceDelete();

        return redirect()
            ->route('admin.bus.trashed')
            ->with('success', 'Bus supprimé définitivement.');
    }

    /**
     * Vérifie que le bus appartient bien à l'agence de l'utilisateur connecté.
     */
    private function authorizeAgence(Bus $bus): void
    {
        abort_if(
            (int) $bus->agence_id !== (int) Auth::user()->agence_id,
            403,
            'Accès non autorisé à ce bus.'
        );
    }
}