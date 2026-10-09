<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlanificationVoyage;
use App\Models\Trajet;
use App\Models\Bus;
use App\Models\VagueDepart;
use App\Models\Agence;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlanificationVoyageController extends Controller
{
    /**
     * Liste des planifications pour l'agence connectée (VUE BLADE).
     */
    public function index()
    {
        $agenceId = Auth::user()->agence_id;

        $planifications = PlanificationVoyage::with([
                'trajet.gareDepart',
                'trajet.gareArrivee',
                'bus',
                'vagueDepart',
                'tickets',
            ])
            ->where('agence_id', $agenceId)
            ->latest('date_voyage')
            ->paginate(15);

        return view('admin.planifications.index', compact('planifications'));
    }

    /**
     * ⭐ API : Liste des planifications pour Flutter (JSON).
     * GET /api/planifications
     */
    public function apiIndex(Request $request)
    {
        $agenceNom = $request->query('agence');

        $query = PlanificationVoyage::with([
            'trajet.gareDepart',
            'trajet.gareArrivee',
            'bus',
            'vagueDepart',
        ])
        ->where('statut_voyage', '!=', 'Annulé');

        // Filtrer par agence si le paramètre est fourni
        if ($agenceNom) {
            $agence = Agence::where('nom_agence', 'LIKE', "%{$agenceNom}%")->first();
            if ($agence) {
                $query->where('agence_id', $agence->id);
            } else {
                return response()->json([]);
            }
        } else {
            $authAgenceId = optional(Auth::user())->agence_id;
            if ($authAgenceId) {
                $query->where('agence_id', $authAgenceId);
            }
        }

        $planifications = $query->orderBy('date_voyage')->get();

        $data = $planifications->map(function ($p) {
            return [
                'id'                   => $p->id,
                'date_voyage'          => $p->date_voyage ? $p->date_voyage->format('Y-m-d') : null,
                'nombre_tickets_total' => (int) $p->nombre_tickets_total,
                'places_disponibles'   => (int) $p->places_disponibles,
                'prix'                 => (float) ($p->prix ?? 0), // 👈 Transmission du prix défini par l'admin
                'statut_voyage'        => $p->statut_voyage,
                'agence_id'            => $p->agence_id,
                'trajet' => $p->trajet ? [
                    'id'           => $p->trajet->id,
                    'gare_depart'  => $p->trajet->gareDepart ? [
                        'id'    => $p->trajet->gareDepart->id,
                        'nom'   => $p->trajet->gareDepart->nom_gare,
                        'ville' => $p->trajet->gareDepart->ville,
                    ] : null,
                    'gare_arrivee' => $p->trajet->gareArrivee ? [
                        'id'    => $p->trajet->gareArrivee->id,
                        'nom'   => $p->trajet->gareArrivee->nom_gare,
                        'ville' => $p->trajet->gareArrivee->ville,
                    ] : null,
                ] : null,
                'bus' => $p->bus ? [
                    'id'              => $p->bus->id,
                    'immatriculation' => $p->bus->immatriculation ?? null,
                    'numero'          => $p->bus->numero ?? null,
                    'modele'          => $p->bus->modele ?? null,
                    'capacite_totale' => $p->bus->capacite_totale ?? $p->bus->places ?? null,
                    'statut'          => $p->bus->statut ?? 'Disponible',
                ] : null,
                'vague_depart' => $p->vagueDepart ? [
                    'id'           => $p->vagueDepart->id,
                    'nom_vague'    => $p->vagueDepart->nom_vague,
                    'heure_depart' => $p->vagueDepart->heure_depart,
                ] : null,
            ];
        });

        return response()->json($data);
    }

    /**
     * Formulaire de création.
     */
    public function create()
    {
        $agenceId = Auth::user()->agence_id;

        $trajets = Trajet::with(['gareDepart', 'gareArrivee', 'bus'])
            ->where('agence_id', $agenceId)
            ->get();

        $buses = Bus::where('agence_id', $agenceId)
            ->orderBy('immatriculation')
            ->get();

        $vagues = VagueDepart::where('agence_id', $agenceId)->get();

        return view('admin.planifications.create', compact('trajets', 'buses', 'vagues'));
    }

    /**
     * ⭐ Enregistre PLUSIEURS planifications avec quota personnalisé de tickets et prix.
     */
    public function store(Request $request)
    {
        $agenceId = Auth::user()->agence_id;

        $request->validate([
            'trajet_id' => [
                'required', 'exists:trajets,id',
                function ($attribute, $value, $fail) use ($agenceId) {
                    $trajet = Trajet::find($value);
                    if (!$trajet || (int) $trajet->agence_id !== (int) $agenceId) {
                        $fail('Le trajet sélectionné est invalide.');
                    }
                },
            ],
            'bus_id' => [
                'required', 'exists:bus,id',
                function ($attribute, $value, $fail) use ($agenceId) {
                    $bus = Bus::find($value);
                    if (!$bus || (int) $bus->agence_id !== (int) $agenceId) {
                        $fail('Le bus sélectionné est invalide.');
                    }
                },
            ],
            'vague_depart_id' => [
                'required', 'exists:vague_departs,id',
                function ($attribute, $value, $fail) use ($agenceId, $request) {
                    $vague = VagueDepart::find($value);
                    if (!$vague || (int) $vague->agence_id !== (int) $agenceId) {
                        $fail('La vague de départ sélectionnée est invalide.');
                        return;
                    }
                    if ($request->filled('trajet_id') && (int) $vague->trajet_id !== (int) $request->trajet_id) {
                        $fail('La vague de départ ne correspond pas au trajet sélectionné.');
                    }
                },
            ],
            'nombre_tickets_total' => 'nullable|integer|min:1',
            'prix'                 => 'required|numeric|min:0', // 👈 Validation du prix
            'dates_voyage'         => 'required|string',
        ]);

        $bus = Bus::findOrFail($request->bus_id);
        $capaciteBus = (int) ($bus->capacite_totale ?? $bus->places ?? 0);
        
        $nombreTickets = $request->filled('nombre_tickets_total') 
            ? (int) $request->nombre_tickets_total 
            : $capaciteBus;

        $dates = array_filter(array_map('trim', explode(',', $request->dates_voyage)));

        if (empty($dates)) {
            return back()
                ->withErrors(['dates_voyage' => 'Veuillez sélectionner au moins une date.'])
                ->withInput();
        }

        $creees   = 0;
        $ignorees = 0;

        foreach ($dates as $date) {
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                $ignorees++;
                continue;
            }

            if ($date < now()->format('Y-m-d')) {
                $ignorees++;
                continue;
            }

            $existe = PlanificationVoyage::where('agence_id', $agenceId)
                ->where('trajet_id', $request->trajet_id)
                ->where('bus_id', $request->bus_id)
                ->where('vague_depart_id', $request->vague_depart_id)
                ->where('date_voyage', $date)
                ->exists();

            if ($existe) {
                $ignorees++;
                continue;
            }

            PlanificationVoyage::create([
                'agence_id'            => $agenceId,
                'trajet_id'            => $request->trajet_id,
                'bus_id'               => $request->bus_id,
                'vague_depart_id'      => $request->vague_depart_id,
                'date_voyage'          => $date,
                'nombre_tickets_total' => $nombreTickets,
                'places_disponibles'   => $nombreTickets,
                'prix'                 => $request->prix, // 👈 Enregistrement du prix
                'statut_voyage'        => 'Programmation',
            ]);

            $creees++;
        }

        $message = "{$creees} planification(s) créée(s).";
        if ($ignorees > 0) {
            $message .= " {$ignorees} date(s) ignorée(s) (déjà existante(s) ou invalide(s)).";
        }

        return redirect()
            ->route('admin.planifications.index')
            ->with('success', $message);
    }

    /**
     * Détails d'une planification.
     */
    public function show(PlanificationVoyage $planificationVoyage)
    {
        $this->authorizeAgence($planificationVoyage);

        $planificationVoyage->load([
            'trajet.gareDepart',
            'trajet.gareArrivee',
            'bus',
            'vagueDepart',
            'tickets',
        ]);

        return view('admin.planifications.show', compact('planificationVoyage'));
    }

    /**
     * Formulaire de modification.
     */
    public function edit(PlanificationVoyage $planificationVoyage)
    {
        $this->authorizeAgence($planificationVoyage);

        $agenceId = Auth::user()->agence_id;

        $trajets = Trajet::with(['gareDepart', 'gareArrivee', 'bus'])
            ->where('agence_id', $agenceId)
            ->get();

        $buses = Bus::where('agence_id', $agenceId)
            ->orderBy('immatriculation')
            ->get();

        $vagues = VagueDepart::where('agence_id', $agenceId)->get();

        return view('admin.planifications.edit', compact(
            'planificationVoyage',
            'trajets',
            'buses',
            'vagues'
        ));
    }

    /**
     * Mise à jour (d'une seule planification).
     */
    public function update(Request $request, PlanificationVoyage $planificationVoyage)
    {
        $this->authorizeAgence($planificationVoyage);

        $agenceId = Auth::user()->agence_id;

        $request->validate([
            'trajet_id' => [
                'required', 'exists:trajets,id',
                function ($attribute, $value, $fail) use ($agenceId) {
                    $trajet = Trajet::find($value);
                    if (!$trajet || (int) $trajet->agence_id !== (int) $agenceId) {
                        $fail('Le trajet sélectionné est invalide.');
                    }
                },
            ],
            'bus_id' => [
                'required', 'exists:bus,id',
                function ($attribute, $value, $fail) use ($agenceId) {
                    $bus = Bus::find($value);
                    if (!$bus || (int) $bus->agence_id !== (int) $agenceId) {
                        $fail('Le bus sélectionné est invalide.');
                    }
                },
            ],
            'vague_depart_id' => [
                'required', 'exists:vague_departs,id',
                function ($attribute, $value, $fail) use ($agenceId, $request) {
                    $vague = VagueDepart::find($value);
                    if (!$vague || (int) $vague->agence_id !== (int) $agenceId) {
                        $fail('La vague de départ sélectionnée est invalide.');
                        return;
                    }
                    if ($request->filled('trajet_id') && (int) $vague->trajet_id !== (int) $request->trajet_id) {
                        $fail('La vague de départ ne correspond pas au trajet sélectionné.');
                    }
                },
            ],
            'nombre_tickets_total' => 'nullable|integer|min:1',
            'prix'                 => 'required|numeric|min:0', // 👈 Validation du prix
            'date_voyage'          => 'required|date',
            'statut_voyage'        => 'required|in:Programmation,En cours,Terminé,Annulé',
        ]);

        $bus = Bus::findOrFail($request->bus_id);
        $capaciteBus = (int) ($bus->capacite_totale ?? $bus->places ?? 0);

        $nombreTicketsTotal = $request->filled('nombre_tickets_total') 
            ? (int) $request->nombre_tickets_total 
            : ($planificationVoyage->nombre_tickets_total ?? $capaciteBus);

        $placesVendues = $planificationVoyage->tickets()->count();
        $placesDisponibles = max(0, $nombreTicketsTotal - $placesVendues);

        $planificationVoyage->update([
            'trajet_id'            => $request->trajet_id,
            'bus_id'               => $request->bus_id,
            'vague_depart_id'      => $request->vague_depart_id,
            'date_voyage'          => $request->date_voyage,
            'nombre_tickets_total' => $nombreTicketsTotal,
            'places_disponibles'   => $placesDisponibles,
            'prix'                 => $request->prix, // 👈 Mise à jour du prix
            'statut_voyage'        => $request->statut_voyage,
        ]);

        return redirect()
            ->route('admin.planifications.index')
            ->with('success', 'Planification mise à jour avec succès.');
    }

    /**
     * Suppression.
     */
    public function destroy(PlanificationVoyage $planificationVoyage)
    {
        $this->authorizeAgence($planificationVoyage);

        $planificationVoyage->delete();

        return redirect()
            ->route('admin.planifications.index')
            ->with('success', 'Planification supprimée avec succès.');
    }

    private function authorizeAgence(PlanificationVoyage $planificationVoyage): void
    {
        abort_if(
            (int) $planificationVoyage->agence_id !== (int) Auth::user()->agence_id,
            403,
            'Accès non autorisé à cette planification de voyage.'
        );
    }
}
