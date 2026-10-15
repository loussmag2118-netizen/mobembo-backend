<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Trajet;
use App\Models\Gare;
use App\Models\Bus;
use App\Models\Agence;
use App\Models\VagueDepart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class TrajetController extends Controller
{
    /**
     * Liste des trajets pour l'agence connectée.
     */
    public function index()
    {
        $trajets = Trajet::with(['gareDepart', 'gareArrivee', 'vagues', 'bus'])
            ->where('agence_id', Auth::user()->agence_id)
            ->latest()
            ->paginate(5)->withQueryString();

        return view('admin.trajets.index', compact('trajets'));
    }

    /**
     * Formulaire de création.
     */
    public function create()
    {
        $agenceId = Auth::user()->agence_id;

        $gares = Gare::where('agence_id', $agenceId)
            ->orderBy('ville')
            ->orderBy('nom_gare')
            ->get();

        $buses = Bus::where('agence_id', $agenceId)
            ->where('statut', '!=', 'Indisponible')
            ->orderBy('immatriculation')
            ->get();

        return view('admin.trajets.create', compact('gares', 'buses'));
    }

    /**
     * Enregistre un nouveau trajet + ses horaires (vagues).
     */
    public function store(Request $request)
    {
        $agenceId = Auth::user()->agence_id;

        $request->validate([
            'gare_depart_id'  => [
                'required', 'exists:gares,id',
                // 🔑 Unicité combinée : (agence_id, gare_depart_id, gare_arrivee_id)
                Rule::unique('trajets', 'gare_depart_id')
                    ->where('agence_id', $agenceId)
                    ->where('gare_arrivee_id', $request->gare_arrivee_id),
                function ($attr, $value, $fail) use ($agenceId) {
                    $gare = Gare::find($value);
                    if (!$gare || (int) $gare->agence_id !== (int) $agenceId) {
                        $fail('La gare de départ est invalide.');
                    }
                },
            ],
            'gare_arrivee_id' => [
                'required', 'exists:gares,id', 'different:gare_depart_id',
                function ($attr, $value, $fail) use ($agenceId) {
                    $gare = Gare::find($value);
                    if (!$gare || (int) $gare->agence_id !== (int) $agenceId) {
                        $fail('La gare d\'arrivée est invalide.');
                    }
                },
            ],
            'bus_id'          => [
                'required', 'exists:bus,id',
                function ($attr, $value, $fail) use ($agenceId) {
                    $bus = Bus::find($value);
                    if (!$bus || (int) $bus->agence_id !== (int) $agenceId) {
                        $fail('Le bus sélectionné est invalide.');
                    }
                },
            ],
            'horaires'        => 'required|array|min:1',
            'horaires.*'      => 'required|date_format:H:i',
            'duree_minutes'     => 'nullable|integer|min:10|max:4320',
            'bagages_autorises' => 'nullable|string|max:255',
            'conditions_voyage' => 'nullable|string|max:2000',
        ], [
            'gare_depart_id.unique'     => 'Un trajet identique (même gare de départ et d\'arrivée) existe déjà pour cette agence.',
            'gare_arrivee_id.different' => 'La gare d\'arrivée doit être différente de la gare de départ.',
        ]);

        [$villeDepart, $villeArrivee] = $this->villesDesGares($request);

        try {
            $trajet = Trajet::create([
                'agence_id'       => $agenceId,
                'ville_depart'    => $villeDepart,
                'gare_depart_id'  => $request->gare_depart_id,
                'ville_arrivee'   => $villeArrivee,
                'gare_arrivee_id' => $request->gare_arrivee_id,
                'bus_id'          => $request->bus_id,
                'duree_minutes'     => $request->filled('duree_minutes') ? (int) $request->duree_minutes : null,
                'bagages_autorises' => $request->filled('bagages_autorises') ? trim($request->bagages_autorises) : null,
                'conditions_voyage' => $request->filled('conditions_voyage') ? trim($request->conditions_voyage) : null,
            ]);

            // ⭐ Crée les vagues (horaires)
            $this->synchroniserVagues($trajet, $request->horaires, $agenceId);
        } catch (UniqueConstraintViolationException $e) {
            return back()
                ->withInput()
                ->withErrors([
                    'gare_depart_id' => 'Ce trajet existe déjà pour cette agence (doublon détecté).',
                ]);
        }

        return redirect()
            ->route('admin.trajets.index')
            ->with('success', 'Trajet et horaires enregistrés avec succès.');
    }

    /**
     * Formulaire de modification.
     */
    public function edit(Trajet $trajet)
    {
        $this->authorizeAgence($trajet);

        $trajet->load(['vagues', 'bus', 'gareDepart', 'gareArrivee']);

        $agenceId = Auth::user()->agence_id;

        $gares = Gare::where('agence_id', $agenceId)
            ->orderBy('ville')
            ->orderBy('nom_gare')
            ->get();

        $buses = Bus::where('agence_id', $agenceId)
            ->where('statut', '!=', 'Indisponible')
            ->orderBy('immatriculation')
            ->get();

        return view('admin.trajets.edit', compact('trajet', 'gares', 'buses'));
    }

    /**
     * Mise à jour d'un trajet + ses horaires.
     */
    public function update(Request $request, Trajet $trajet)
    {
        $this->authorizeAgence($trajet);
        $agenceId = Auth::user()->agence_id;

        $request->validate([
            'gare_depart_id'  => [
                'required', 'exists:gares,id',
                // 🔑 Unicité combinée, en ignorant le trajet en cours
                Rule::unique('trajets', 'gare_depart_id')
                    ->where('agence_id', $agenceId)
                    ->where('gare_arrivee_id', $request->gare_arrivee_id)
                    ->ignore($trajet->id),
                function ($attr, $value, $fail) use ($agenceId) {
                    $gare = Gare::find($value);
                    if (!$gare || (int) $gare->agence_id !== (int) $agenceId) {
                        $fail('La gare de départ est invalide.');
                    }
                },
            ],
            'gare_arrivee_id' => [
                'required', 'exists:gares,id', 'different:gare_depart_id',
                function ($attr, $value, $fail) use ($agenceId) {
                    $gare = Gare::find($value);
                    if (!$gare || (int) $gare->agence_id !== (int) $agenceId) {
                        $fail('La gare d\'arrivée est invalide.');
                    }
                },
            ],
            'bus_id'          => [
                'required', 'exists:bus,id',
                function ($attr, $value, $fail) use ($agenceId) {
                    $bus = Bus::find($value);
                    if (!$bus || (int) $bus->agence_id !== (int) $agenceId) {
                        $fail('Le bus sélectionné est invalide.');
                    }
                },
            ],
            'horaires'        => 'required|array|min:1',
            'horaires.*'      => 'required|date_format:H:i',
            'duree_minutes'     => 'nullable|integer|min:10|max:4320',
            'bagages_autorises' => 'nullable|string|max:255',
            'conditions_voyage' => 'nullable|string|max:2000',
        ], [
            'gare_depart_id.unique'     => 'Un trajet identique (même gare de départ et d\'arrivée) existe déjà pour cette agence.',
            'gare_arrivee_id.different' => 'La gare d\'arrivée doit être différente de la gare de départ.',
        ]);

        [$villeDepart, $villeArrivee] = $this->villesDesGares($request);

        try {
            $trajet->update([
                'ville_depart'    => $villeDepart,
                'gare_depart_id'  => $request->gare_depart_id,
                'ville_arrivee'   => $villeArrivee,
                'gare_arrivee_id' => $request->gare_arrivee_id,
                'bus_id'          => $request->bus_id,
                'duree_minutes'     => $request->filled('duree_minutes') ? (int) $request->duree_minutes : null,
                'bagages_autorises' => $request->filled('bagages_autorises') ? trim($request->bagages_autorises) : null,
                'conditions_voyage' => $request->filled('conditions_voyage') ? trim($request->conditions_voyage) : null,
            ]);

            // ⭐ Synchronise les vagues
            $this->synchroniserVagues($trajet, $request->horaires, $agenceId);
        } catch (UniqueConstraintViolationException $e) {
            return back()
                ->withInput()
                ->withErrors([
                    'gare_depart_id' => 'Ce trajet existe déjà pour cette agence (doublon détecté).',
                ]);
        }

        return redirect()
            ->route('admin.trajets.index')
            ->with('success', 'Trajet et horaires mis à jour avec succès.');
    }

    /**
     * Suppression d'un trajet (soft delete).
     */
    public function destroy(Trajet $trajet)
    {
        $this->authorizeAgence($trajet);
        $trajet->delete();

        return redirect()
            ->route('admin.trajets.index')
            ->with('success', 'Trajet supprimé avec succès.');
    }

    /**
     * API : Retourne les trajets avec GARES + VAGUES + BUS.
     * GET /api/agence-donnees?agence=XXX
     */
    public function apiAgenceDonnees(Request $request)
    {
        $nomAgence = $request->query('agence');
        $agence = Agence::where('nom_agence', 'LIKE', "%{$nomAgence}%")->first();

        if (!$agence) {
            return response()->json(['trajets' => []], 404);
        }

        $trajets = Trajet::with(['gareDepart', 'gareArrivee', 'vagues', 'bus'])
            ->where('agence_id', $agence->id)
            ->get()
            ->map(function ($trajet) {
                return [
                    'id'            => $trajet->id,
                    'ville_depart'  => $trajet->ville_depart,
                    'ville_arrivee' => $trajet->ville_arrivee,
                    'gare_depart'   => [
                        'id'    => $trajet->gareDepart->id ?? null,
                        'nom'   => $trajet->gareDepart->nom_gare ?? '',
                        'ville' => $trajet->gareDepart->ville ?? '',
                    ],
                    'gare_arrivee'  => [
                        'id'    => $trajet->gareArrivee->id ?? null,
                        'nom'   => $trajet->gareArrivee->nom_gare ?? '',
                        'ville' => $trajet->gareArrivee->ville ?? '',
                    ],
                    'bus'           => $trajet->bus ? [
                        'id'              => $trajet->bus->id,
                        'immatriculation' => $trajet->bus->immatriculation,
                        'modele'          => $trajet->bus->modele,
                        'capacite_totale' => $trajet->bus->capacite_totale,
                        'statut'          => $trajet->bus->statut,
                    ] : null,
                    'vagues'        => $trajet->vagues->map(function ($v) {
                        return [
                            'id'           => $v->id,
                            'nom_vague'    => $v->nom_vague,
                            'heure_depart' => $v->heure_depart,
                        ];
                    })->values(),
                ];
            });

        return response()->json(['trajets' => $trajets]);
    }

    // ══════════════════════════════════════════════════════════════
    // MÉTHODES PRIVÉES
    // ══════════════════════════════════════════════════════════════

    /**
     * Les villes d'un trajet sont celles de ses gares (référentiel Super Admin) :
     * l'admin ne les saisit plus. Retourne [ville_depart, ville_arrivee].
     */
    private function villesDesGares(Request $request): array
    {
        $villeDepart  = Gare::whereKey($request->gare_depart_id)->value('ville');
        $villeArrivee = Gare::whereKey($request->gare_arrivee_id)->value('ville');

        if (mb_strtolower(trim($villeDepart)) === mb_strtolower(trim($villeArrivee))) {
            throw ValidationException::withMessages([
                'gare_arrivee_id' => "Les deux gares sont à {$villeDepart} : la ville d'arrivée doit être différente de la ville de départ.",
            ]);
        }

        return [$villeDepart, $villeArrivee];
    }

    private function synchroniserVagues(Trajet $trajet, array $horaires, int $agenceId): void
    {
        $horaires = array_filter(array_map('trim', $horaires));
        $horaires = array_unique($horaires);
        $horaires = array_values($horaires);

        VagueDepart::where('trajet_id', $trajet->id)
            ->whereNotIn('heure_depart', $horaires)
            ->delete();

        foreach ($horaires as $heure) {
            if (empty($heure)) {
                continue;
            }

            $vague = VagueDepart::withTrashed()
                ->where('trajet_id', $trajet->id)
                ->where('heure_depart', $heure)
                ->first();

            if ($vague) {
                if ($vague->trashed()) {
                    $vague->restore();
                }
                $vague->update([
                    'agence_id' => $agenceId,
                    'nom_vague' => 'Départ ' . $heure,
                ]);
            } else {
                VagueDepart::create([
                    'agence_id'    => $agenceId,
                    'trajet_id'    => $trajet->id,
                    'nom_vague'    => 'Départ ' . $heure,
                    'heure_depart' => $heure,
                ]);
            }
        }
    }

    private function authorizeAgence(Trajet $trajet): void
    {
        abort_if(
            (int) $trajet->agence_id !== (int) Auth::user()->agence_id,
            403,
            'Accès non autorisé à ce trajet.'
        );
    }

    /**
     * ⭐ API Mobile : Liste des trajets en JSON.
     * GET /api/trajets
     */
    public function apiIndex(Request $request)
    {
        try {
            $agenceNom = $request->query('agence');

            $query = Trajet::with(['gareDepart', 'gareArrivee', 'vagues', 'bus']);

            if ($agenceNom) {
                $query->whereHas('agence', function ($q) use ($agenceNom) {
                    $q->where('nom_agence', 'LIKE', "%{$agenceNom}%");
                });
            }

            $trajets = $query->get()->map(fn($t) => [
                'id'              => $t->id,
                'ville_depart'    => $t->ville_depart,
                'ville_arrivee'   => $t->ville_arrivee,
                'gare_depart_id'  => $t->gare_depart_id,
                'gare_arrivee_id' => $t->gare_arrivee_id,
                'agence_id'       => $t->agence_id,
                'prix'            => $t->prix,
                'duree_estimee'   => $t->duree_estimee,
                'gare_depart'     => $t->gareDepart ? [
                    'id'       => $t->gareDepart->id,
                    'nom_gare' => $t->gareDepart->nom_gare,
                    'ville'    => $t->gareDepart->ville,
                ] : null,
                'gare_arrivee' => $t->gareArrivee ? [
                    'id'       => $t->gareArrivee->id,
                    'nom_gare' => $t->gareArrivee->nom_gare,
                    'ville'    => $t->gareArrivee->ville,
                ] : null,
                'horaires' => $t->vagues->pluck('heure_depart')->values(),
            ])->values();

            return response()->json($trajets, 200);
        } catch (Throwable $e) {
            return response()->json(['message' => 'Erreur : ' . $e->getMessage()], 500);
        }
    }
}