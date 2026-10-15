<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PlanificationVoyage;
use App\Models\Ticket;
use App\Models\VilleTerrestre;
use App\Services\PaiementService;
use App\Services\ReservationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

/**
 * Parcours client du bus : villes, recherche de voyages toutes agences
 * confondues, et consultation de ses billets.
 */
class ClientBusController extends Controller
{
    public function __construct(private ReservationService $reservations)
    {
    }

    // GET /api/villes-terrestres
    public function villes(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'villes'  => VilleTerrestre::where('actif', true)->orderBy('nom')->pluck('nom'),
        ]);
    }

    /**
     * GET /api/voyages/recherche?ville_depart=&ville_arrivee=&date=YYYY-MM-DD&passagers=1
     *
     * Uniquement les départs réels configurés par les agences : ouverts à la
     * réservation, d'une agence active, ce jour-là, avec assez de places.
     */
    public function rechercher(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'ville_depart'  => 'required|string|max:100',
            'ville_arrivee' => 'required|string|max:100|different:ville_depart',
            'date'          => 'required|date_format:Y-m-d|after_or_equal:today',
            'passagers'     => 'nullable|integer|min:1|max:10',
        ], [
            'ville_arrivee.different' => "La ville d'arrivée doit être différente de la ville de départ.",
            'date.after_or_equal'     => 'Choisissez une date à partir d\'aujourd\'hui.',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
        }

        // Les réservations non payées expirées libèrent leurs places avant l'affichage
        $this->reservations->expirerReservations();

        $passagers = (int) ($request->passagers ?? 1);
        $depart    = mb_strtolower(trim($request->ville_depart));
        $arrivee   = mb_strtolower(trim($request->ville_arrivee));

        $departs = PlanificationVoyage::with(['agence.logoMedia', 'trajet.gareDepart', 'trajet.gareArrivee', 'vagueDepart'])
            ->where('statut_voyage', 'Programmation')
            ->whereDate('date_voyage', $request->date)
            ->where('places_disponibles', '>=', $passagers)
            ->whereHas('agence', fn ($q) => $q->where('statut', 'Actif'))
            ->whereHas('trajet', function ($q) use ($depart, $arrivee) {
                $q->whereRaw('LOWER(ville_depart) = ?', [$depart])
                  ->whereRaw('LOWER(ville_arrivee) = ?', [$arrivee]);
            })
            ->get()
            // Départ du jour dont l'heure est passée : plus proposé
            ->filter(function (PlanificationVoyage $v) {
                $heure = $v->vagueDepart?->heure_depart;
                if (! $heure || ! $v->date_voyage_carbon?->isToday()) {
                    return true;
                }

                return Carbon::parse($v->date_voyage_carbon->toDateString() . ' ' . $heure)->isFuture();
            })
            ->sortBy(fn ($v) => $v->vagueDepart?->heure_depart ?? '99:99')
            ->values()
            ->map(fn (PlanificationVoyage $v) => [
                'planification_id'   => $v->id,
                'agence'             => ['id' => $v->agence?->id, 'nom' => $v->agence?->nom_agence, 'logo' => $v->agence?->logo_app, 'logo_url' => $v->agence?->logoMedia?->url(), 'couleur' => $v->agence?->couleur],
                'ville_depart'       => $v->trajet?->ville_depart,
                'ville_arrivee'      => $v->trajet?->ville_arrivee,
                'gare_depart'        => $v->trajet?->gareDepart?->nom_gare,
                'quartier_depart'    => $v->trajet?->gareDepart?->quartier,
                'gare_arrivee'       => $v->trajet?->gareArrivee?->nom_gare,
                'date'               => $v->date_voyage_carbon?->toDateString(),
                'heure_depart'       => $v->vagueDepart?->heure_depart ? substr($v->vagueDepart->heure_depart, 0, 5) : null,
                'nom_vague'          => $v->vagueDepart?->nom_vague,
                'prix'               => (float) $v->prix,
                'prix_enfant'        => $v->prix_enfant !== null ? (float) $v->prix_enfant : null,   // null = prix adulte
                'age_max_enfant'     => (int) ($v->agence?->age_max_enfant ?? 12),
                'choix_siege'        => (bool) $v->agence?->choix_siege,
                'places_disponibles' => (int) $v->places_disponibles,
                // Facultatifs : null si l'agence ne les a pas renseignés
                'duree_minutes'      => $v->trajet?->duree_minutes,
                'bagages_autorises'  => $v->trajet?->bagages_autorises,
                'conditions_voyage'  => $v->trajet?->conditions_voyage,
            ]);

        return response()->json([
            'success'  => true,
            'nombre'   => $departs->count(),
            'voyages'  => $departs,
            'message'  => $departs->isEmpty() ? 'Aucun départ disponible pour ces critères.' : null,
        ]);
    }

    /**
     * GET /api/departs/{id}/sieges — plan du bus et sièges déjà pris.
     * Aucune donnée personnelle : seulement les numéros occupés.
     */
    public function sieges(int $id): JsonResponse
    {
        $this->reservations->expirerReservations();

        $voyage = PlanificationVoyage::with(['agence', 'bus'])
            ->whereKey($id)
            ->where('statut_voyage', 'Programmation')
            ->whereHas('agence', fn ($q) => $q->where('statut', 'Actif'))
            ->first();

        if (! $voyage) {
            return response()->json(['message' => "Ce départ n'est plus disponible."], 404);
        }

        return response()->json(['success' => true, 'planification_id' => $voyage->id] + $this->reservations->planSieges($voyage));
    }

    // GET /api/mes-billets — billets du client connecté
    public function mesBillets(Request $request): JsonResponse
    {
        $this->reservations->expirerReservations();

        $tickets = Ticket::with(['agence.logoMedia', 'planificationVoyage.trajet.gareDepart', 'planificationVoyage.trajet.gareArrivee'])
            ->where('utilisateur_id', $request->user()->id)
            ->orderByDesc('date_reservation')
            ->limit(100)
            ->get()
            ->map(function (Ticket $t) {
                $voyage = $t->planificationVoyage;
                $paye   = $t->statut_paiement === 'Paye';

                return [
                    'id'                  => $t->id,
                    'reference'           => $t->reference_ticket,
                    'reference_paiement'  => $t->reference_paiement,
                    'agence'              => $t->agence?->nom_agence,
                    'agence_logo'         => $t->agence?->logo_app,
                    'agence_logo_url'     => $t->agence?->logoMedia?->url(),
                    'agence_couleur'      => $t->agence?->couleur,
                    'nom'                 => $t->nom_passager,
                    'prenom'              => $t->prenom_passager,
                    'categorie'           => $t->categorie_passager ?? 'Adulte',
                    'age'                 => $t->age_passager,
                    'siege'               => $t->numero_siege,
                    'ville_depart'        => $t->ville_depart ?? $voyage?->trajet?->ville_depart,
                    'ville_arrivee'       => $t->ville_arrivee ?? $voyage?->trajet?->ville_arrivee,
                    'gare_depart'         => $voyage?->trajet?->gareDepart?->nom_gare,
                    'gare_arrivee'        => $voyage?->trajet?->gareArrivee?->nom_gare,
                    'date'                => $voyage?->date_voyage_carbon?->toDateString(),
                    'heure_depart'        => $t->horaire ? substr($t->horaire, 0, 5) : null,
                    'numero_bus'          => $t->numero_bus,
                    'prix'                => (float) $t->montant_total,
                    'mode_paiement'       => $t->mode_paiement,
                    'statut'              => $this->statutClient($t),
                    'statut_paiement'     => $t->statut_paiement,
                    'statut_embarquement' => $t->statut_embarquement,
                    'expire_le'           => $t->statut_paiement === 'En_Attente' ? $t->expire_le?->toIso8601String() : null,
                    'billet_test'         => PaiementService::estReferenceTest($t->reference_paiement),
                    // Le QR n'est fourni que pour un billet payé
                    'qr'                  => $paye ? 'MBL:' . $t->qr_code_token : null,
                    // Contenu du QR : page du billet (logo et informations) lisible par tout téléphone
                    'qr_url'              => $paye ? $t->urlPublique() : null,
                ];
            });

        return response()->json(['success' => true, 'billets' => $tickets]);
    }

    /** Statut lisible par le client */
    private function statutClient(Ticket $t): string
    {
        if ($t->statut_paiement === 'Paye') {
            return $t->statut_embarquement === 'Present' ? 'UTILISE' : 'CONFIRME';
        }

        return match ($t->statut_paiement) {
            'En_Attente' => 'EN_ATTENTE_PAIEMENT',
            'Rembourse'  => 'REMBOURSE',
            default      => 'ANNULE',
        };
    }
}
