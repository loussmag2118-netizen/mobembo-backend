<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VagueDepart;
use App\Models\Trajet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Validation\Rule;

class VagueDepartController extends Controller
{
    /**
     * Liste des vagues de l'agence connectée.
     */
    public function index()
    {
        $agenceId = Auth::user()->agence_id;

        $vagues = VagueDepart::with('trajet')
            ->where('agence_id', $agenceId)
            ->orderBy('heure_depart')
            ->get();

        return view('admin.vagues.index', compact('vagues'));
    }

    /**
     * Formulaire de création.
     */
    public function create()
    {
        $agenceId = Auth::user()->agence_id;

        // On a besoin de la liste des trajets pour lier la vague
        $trajets = Trajet::with(['gareDepart', 'gareArrivee'])
            ->where('agence_id', $agenceId)
            ->orderBy('ville_depart')
            ->get();

        return view('admin.vagues.create', compact('trajets'));
    }

    /**
     * Enregistre une nouvelle vague.
     */
    public function store(Request $request)
    {
        $agenceId = Auth::user()->agence_id;

        $request->validate([
            'trajet_id' => [
                'required',
                'exists:trajets,id',
                function ($attr, $value, $fail) use ($agenceId) {
                    $trajet = Trajet::find($value);
                    if (!$trajet || (int) $trajet->agence_id !== (int) $agenceId) {
                        $fail('Le trajet sélectionné est invalide.');
                    }
                },
            ],
            'nom_vague' => 'required|string|max:100',
            'heure_depart' => [
                'required',
                'date_format:H:i',
                // 🔑 Unicité sur (agence_id, trajet_id, heure_depart)
                //    → à adapter si la contrainte en base change
                Rule::unique('vague_departs', 'heure_depart')
                    ->where('agence_id', $agenceId)
                    ->where('trajet_id', $request->trajet_id),
            ],
        ], [
            'heure_depart.unique' => 'Une vague existe déjà à cette heure pour ce trajet.',
        ]);

        try {
            VagueDepart::create([
                'agence_id'    => $agenceId,
                'trajet_id'    => $request->trajet_id,
                'nom_vague'    => $request->nom_vague,
                'heure_depart' => $request->heure_depart,
            ]);
        } catch (UniqueConstraintViolationException $e) {
            return back()
                ->withInput()
                ->withErrors([
                    'heure_depart' => 'Une vague existe déjà à cette heure pour ce trajet.',
                ]);
        }

        return redirect()
            ->route('admin.vagues.index')
            ->with('success', 'Vague de départ ajoutée avec succès.');
    }

    /**
     * Formulaire de modification.
     */
    public function edit(VagueDepart $vague)
    {
        $this->authorizeAgence($vague);

        $agenceId = Auth::user()->agence_id;

        $trajets = Trajet::with(['gareDepart', 'gareArrivee'])
            ->where('agence_id', $agenceId)
            ->orderBy('ville_depart')
            ->get();

        return view('admin.vagues.edit', compact('vague', 'trajets'));
    }

    /**
     * Mise à jour d'une vague.
     */
    public function update(Request $request, VagueDepart $vague)
    {
        $this->authorizeAgence($vague);
        $agenceId = Auth::user()->agence_id;

        $request->validate([
            'trajet_id' => [
                'required',
                'exists:trajets,id',
                function ($attr, $value, $fail) use ($agenceId) {
                    $trajet = Trajet::find($value);
                    if (!$trajet || (int) $trajet->agence_id !== (int) $agenceId) {
                        $fail('Le trajet sélectionné est invalide.');
                    }
                },
            ],
            'nom_vague' => 'required|string|max:100',
            'heure_depart' => [
                'required',
                'date_format:H:i',
                Rule::unique('vague_departs', 'heure_depart')
                    ->where('agence_id', $agenceId)
                    ->where('trajet_id', $request->trajet_id)
                    ->ignore($vague->id),
            ],
        ], [
            'heure_depart.unique' => 'Une autre vague existe déjà à cette heure pour ce trajet.',
        ]);

        try {
            $vague->update([
                'trajet_id'    => $request->trajet_id,
                'nom_vague'    => $request->nom_vague,
                'heure_depart' => $request->heure_depart,
            ]);
        } catch (UniqueConstraintViolationException $e) {
            return back()
                ->withInput()
                ->withErrors([
                    'heure_depart' => 'Une autre vague existe déjà à cette heure pour ce trajet.',
                ]);
        }

        return redirect()
            ->route('admin.vagues.index')
            ->with('success', 'Vague mise à jour avec succès.');
    }

    /**
     * Suppression d'une vague.
     */
    public function destroy(VagueDepart $vague)
    {
        $this->authorizeAgence($vague);
        $vague->delete();

        return redirect()
            ->route('admin.vagues.index')
            ->with('success', 'Vague supprimée avec succès.');
    }

    /**
     * Vérifie que la vague appartient à l'agence connectée.
     */
    private function authorizeAgence(VagueDepart $vague): void
    {
        abort_if(
            (int) $vague->agence_id !== (int) Auth::user()->agence_id,
            403,
            'Accès non autorisé à cette vague.'
        );
    }
}