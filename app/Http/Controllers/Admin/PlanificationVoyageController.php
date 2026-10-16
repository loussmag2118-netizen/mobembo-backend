<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlanificationVoyage;
use App\Support\Audit;
use App\Models\Ticket;
use App\Models\Trajet;
use App\Models\Bus;
use App\Models\VagueDepart;
use App\Models\Agence;
use App\Services\ReservationService;
use Illuminate\Support\Facades\DB;
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
            ->paginate(5)->withQueryString();

        return view('admin.planifications.index', compact('planifications'));
    }

    /**
     * ⭐ API : Liste des planifications pour Flutter (JSON).
     * GET /api/planifications
     */
    public function apiIndex(Request $request, ReservationService $reservations)
    {
        // Libère les réservations non payées dont le délai est dépassé
        $reservations->expirerReservations();

        $agenceNom = $request->query('agence');

        $query = PlanificationVoyage::with([
            'agence',
            'trajet.gareDepart',
            'trajet.gareArrivee',
            'bus',
            'vagueDepart',
        ])
        // Seuls les départs encore réservables : ouverts et pas encore passés
        ->where('statut_voyage', 'Programmation')
        ->whereDate('date_voyage', '>=', today());

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

        $planifications = $query->orderBy('date_voyage')->get()
            // Départ du jour dont le bus est déjà parti : plus proposé
            ->reject(fn (PlanificationVoyage $p) => $reservations->departParti($p))
            ->values();

        $data = $planifications->map(function ($p) {
            return [
                'id'                   => $p->id,
                'date_voyage'          => $p->date_voyage ? $p->date_voyage->format('Y-m-d') : null,
                'nombre_tickets_total' => (int) $p->nombre_tickets_total,
                'places_disponibles'   => (int) $p->places_disponibles,
                'prix'                 => (float) ($p->prix ?? 0), // 👈 Transmission du prix défini par l'admin
                'statut_voyage'        => $p->statut_voyage,
                'agence_id'            => $p->agence_id,
                'prix_enfant'          => $p->prix_enfant !== null ? (float) $p->prix_enfant : null, // null = prix adulte
                'age_max_enfant'       => (int) ($p->agence?->age_max_enfant ?? 12),
                // Bébé de 0 à 3 ans sur les genoux d'un adulte : assurance seulement
                'assurance_bebe'       => (int) config('paiement.assurance_bebe', 5000),
                'age_max_bebe'         => (int) config('paiement.age_max_bebe', 3),
                'choix_siege'          => (bool) $p->agence?->choix_siege,
                'trajet' => $p->trajet ? [
                    'id'            => $p->trajet->id,
                    'ville_depart'  => $p->trajet->ville_depart,
                    'ville_arrivee' => $p->trajet->ville_arrivee,
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
                    // Informations facultatives saisies par l'agence
                    'duree_minutes'     => $p->trajet->duree_minutes,
                    'bagages_autorises' => $p->trajet->bagages_autorises,
                    'conditions_voyage' => $p->trajet->conditions_voyage,
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
            'prix'                 => 'required|numeric|min:1', // Mobile Money n'encaisse pas 0
            'prix_enfant'          => 'nullable|numeric|min:0|lte:prix',
            'dates_voyage'         => 'required|string',
        ]);

        $bus = Bus::findOrFail($request->bus_id);
        $capaciteBus = (int) ($bus->capacite_totale ?? $bus->places ?? 0);
        
        $nombreTickets = $request->filled('nombre_tickets_total') 
            ? (int) $request->nombre_tickets_total 
            : $capaciteBus;

        if ($nombreTickets > $capaciteBus) {
            return back()->withErrors(['nombre_tickets_total' => "Ce bus n'a que {$capaciteBus} places : impossible de vendre {$nombreTickets} billets."])->withInput();
        }

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
                'prix_enfant'          => $request->filled('prix_enfant') ? $request->prix_enfant : null,
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
            'prix'                 => 'required|numeric|min:1', // Mobile Money n'encaisse pas 0
            'prix_enfant'          => 'nullable|numeric|min:0|lte:prix',
            'date_voyage'          => 'required|date',
            'statut_voyage'        => 'required|in:Programmation,En cours,Terminé,Annulé',
        ]);

        $bus = Bus::findOrFail($request->bus_id);
        $capaciteBus = (int) ($bus->capacite_totale ?? $bus->places ?? 0);

        $nombreTicketsTotal = $request->filled('nombre_tickets_total') 
            ? (int) $request->nombre_tickets_total 
            : min($planificationVoyage->nombre_tickets_total ?? $capaciteBus, $capaciteBus);

        if ($nombreTicketsTotal > $capaciteBus) {
            return back()->withErrors(['nombre_tickets_total' => "Ce bus n'a que {$capaciteBus} places : impossible de vendre {$nombreTicketsTotal} billets."])->withInput();
        }

        $reservations = app(ReservationService::class);
        $occupation = $reservations->occupation($planificationVoyage);

        // Des clients ont déjà leur place : on ne peut pas la leur retirer
        if ($occupation['places'] > 0) {
            if ((int) $request->trajet_id !== (int) $planificationVoyage->trajet_id) {
                return back()->withErrors(['trajet_id' => "{$occupation['places']} billet(s) déjà vendu(s) pour ce trajet : le trajet ne peut plus être changé. Annulez ce départ et créez-en un autre."])->withInput();
            }
            if ($nombreTicketsTotal < $occupation['places']) {
                return back()->withErrors(['nombre_tickets_total' => "{$occupation['places']} place(s) déjà vendue(s) ou réservée(s) : le départ doit garder au moins {$occupation['places']} places."])->withInput();
            }
            if ($occupation['siege_max'] > $nombreTicketsTotal) {
                return back()->withErrors(['bus_id' => "Le siège n°{$occupation['siege_max']} est déjà vendu : ce départ doit garder au moins {$occupation['siege_max']} places (bus plus grand ou même bus)."])->withInput();
            }
        }

        // Places restantes recalculées à partir des tickets qui occupent
        // réellement une place (payés ou réservés non expirés)
        DB::transaction(function () use ($request, $planificationVoyage, $nombreTicketsTotal) {
            $voyage = PlanificationVoyage::whereKey($planificationVoyage->id)->lockForUpdate()->first();

            $voyage->update([
                'trajet_id'            => $request->trajet_id,
                'bus_id'               => $request->bus_id,
                'vague_depart_id'      => $request->vague_depart_id,
                'date_voyage'          => $request->date_voyage,
                'nombre_tickets_total' => $nombreTicketsTotal,
                'prix'                 => $request->prix, // 👈 Mise à jour du prix
                'prix_enfant'          => $request->filled('prix_enfant') ? $request->prix_enfant : null,
                'statut_voyage'        => $request->statut_voyage,
            ]);

            // Billets déjà vendus : bus et horaire mis à jour (ils sont imprimés sur le billet)
            $voyage = $voyage->fresh(['bus', 'vagueDepart']);
            Ticket::where('planification_voyage_id', $voyage->id)
                ->whereIn('statut_paiement', ['Paye', 'En_Attente'])
                ->update([
                    'numero_bus' => $voyage->bus?->immatriculation,
                    'horaire'    => $voyage->vagueDepart?->heure_depart,
                    'nom_vague'  => $voyage->vagueDepart?->nom_vague,
                ]);

            app(ReservationService::class)->recalculerPlaces($voyage);
        });

        $payes = Ticket::where('planification_voyage_id', $planificationVoyage->id)->where('statut_paiement', 'Paye')->count();
        if ($request->statut_voyage === 'Annulé' && $payes > 0) {
            Audit::log('admin.depart.annule_avec_billets', $planificationVoyage, ['billets_payes' => $payes]);

            return redirect()
                ->route('admin.planifications.index')
                ->with('success', "Départ annulé. Attention : {$payes} billet(s) payé(s) à rembourser ou à reporter sur un autre départ.");
        }

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

        $occupees = app(ReservationService::class)->occupation($planificationVoyage)['places'];
        if ($occupees > 0) {
            return back()->with('error', "Ce départ a {$occupees} billet(s) payé(s) ou réservé(s) : il ne peut pas être supprimé. Passez-le en « Annulé » pour arrêter les ventes, puis remboursez les clients.");
        }

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

    /**
     * Âge maximum « enfant » de l'agence (prix enfant des départs).
     */
    public function majAgeEnfant(Request $request)
    {
        $request->validate(['age_max_enfant' => 'required|integer|min:1|max:17']);

        $agence = Auth::user()->agence;
        abort_unless($agence, 403);

        $agence->update(['age_max_enfant' => (int) $request->age_max_enfant]);
        \App\Support\Audit::log('agence.age_max_enfant', $agence, ['age_max_enfant' => (int) $request->age_max_enfant]);

        return back()->with('success', "Tarif enfant : jusqu'à {$agence->age_max_enfant} ans.");
    }

    /**
     * Plan des sièges d'un départ pour l'administration (JSON, rafraîchi
     * automatiquement par la page du départ) : qui occupe quel siège.
     */
    public function sieges(PlanificationVoyage $planificationVoyage, ReservationService $reservations)
    {
        $this->authorizeAgence($planificationVoyage);
        $reservations->expirerReservations();

        $details = $planificationVoyage->tickets()
            ->whereIn('statut_paiement', ['Paye', 'En_Attente'])
            ->get()
            ->map(fn ($t) => [
                'siege'     => $t->numero_siege,
                'nom'       => trim($t->nom_passager . ' ' . $t->prenom_passager),
                'categorie' => $t->categorie_passager ?? 'Adulte',
                'statut'    => $t->statut_paiement,
                'present'   => $t->statut_embarquement === 'Present',
                'reference' => $t->reference_ticket,
            ])->values();

        return response()->json([
            'plan'          => $reservations->planSieges($planificationVoyage->fresh()),
            'details'       => $details,
            'sans_siege'    => $details->whereNull('siege')->count(),
            'places_libres' => (int) $planificationVoyage->fresh()->places_disponibles,
            'mis_a_jour'    => now()->format('H:i:s'),
        ]);
    }

    /**
     * Active ou désactive le choix du siège par les clients de l'agence.
     */
    public function majChoixSiege(Request $request)
    {
        $agence = Auth::user()->agence;
        abort_unless($agence, 403);

        $actif = $request->boolean('choix_siege');
        $agence->update(['choix_siege' => $actif]);
        \App\Support\Audit::log('agence.choix_siege', $agence, ['choix_siege' => $actif]);

        return back()->with('success', $actif
            ? 'Les clients choisissent désormais leur siège.'
            : 'Le choix du siège est désactivé : les sièges seront attribués automatiquement.');
    }
}
