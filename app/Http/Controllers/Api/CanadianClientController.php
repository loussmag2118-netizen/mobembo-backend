<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\ReservationImpossibleException;
use App\Http\Controllers\Controller;
use App\Models\Extra;
use App\Models\Notification;
use App\Models\Reservation;
use App\Models\Vol;
use App\Services\ReservationVolService;
use App\Support\Audit;
use App\Support\FormatVol;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

/**
 * API des clients de l'application aérienne : vols de toutes les
 * compagnies aériennes actives, réservations, notifications, profil.
 * Le paiement est géré par PaiementVolController.
 */
class CanadianClientController extends Controller
{
    public function __construct(private ReservationVolService $reservations)
    {
    }

    // ═══════════════════════════════════════════════════════════════
    // VOLS
    // ═══════════════════════════════════════════════════════════════

    /** GET /api/canadian/villes-vols : villes desservies et prochains vols */
    public function villesVols()
    {
        $tous = Vol::enVente()->get(['ville_depart', 'ville_arrivee']);

        $vols = Vol::enVente()->with('partenaire.logoMedia')
            ->where('places_disponibles', '>', 0)
            ->orderBy('date_depart')
            ->limit(20)
            ->get()
            ->map(fn ($v) => FormatVol::pourMobile($v))
            ->values();

        return response()->json([
            'success' => true,
            'villes'  => $tous->pluck('ville_depart')->merge($tous->pluck('ville_arrivee'))->filter()->unique()->sort()->values(),
            'vols'    => $vols,
        ]);
    }

    /** POST /api/canadian/recherche-vols */
    public function rechercheVols(Request $request)
    {
        $donnees = [
            'ville_depart'  => $request->input('ville_depart', $request->input('depart', $request->input('origine', ''))),
            'ville_arrivee' => $request->input('ville_arrivee', $request->input('arrivee', $request->input('destination', ''))),
            'date_depart'   => $request->input('date_depart', $request->input('date')),
        ];

        $validator = Validator::make($donnees, [
            'ville_depart'  => 'required|string|max:100',
            'ville_arrivee' => 'required|string|max:100',
            'date_depart'   => 'nullable|date',
        ], [
            'ville_depart.required'  => 'La ville de départ est obligatoire.',
            'ville_arrivee.required' => 'La ville d\'arrivée est obligatoire.',
            'date_depart.date'       => 'La date de départ est invalide.',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first(), 'vols' => []], 422);
        }

        $query = Vol::enVente()->with('partenaire.logoMedia')
            ->where('ville_depart', $donnees['ville_depart'])
            ->where('ville_arrivee', $donnees['ville_arrivee']);
        if (! empty($donnees['date_depart'])) {
            $query->whereDate('date_depart', $donnees['date_depart']);
        }

        return response()->json([
            'success' => true,
            'vols'    => $query->orderBy('date_depart')->get()->map(fn ($v) => FormatVol::pourMobile($v))->values(),
        ]);
    }

    /** GET /api/canadian/extras?vol_id= : options proposées par la compagnie du vol */
    public function listExtras(Request $request)
    {
        $agenceId = $request->filled('vol_id')
            ? Vol::whereKey($request->integer('vol_id'))->value('agence_id')
            : null;

        $extras = Extra::where('actif', true)
            ->when($agenceId, fn ($q) => $q->where('agence_id', $agenceId))
            ->orderBy('ordre')
            ->get()
            ->map(fn ($e) => [
                'id'           => $e->id,
                'compagnie_id' => $e->agence_id,
                'code'         => $e->code,
                'nom'          => $e->nom,
                'description'  => $e->description,
                'categorie'    => $e->categorie,
                'prix'         => (float) ($e->prix ?? 0),
                'icone'        => $e->icone,
            ])
            ->values();

        return response()->json(['success' => true, 'extras' => $extras]);
    }

    // ═══════════════════════════════════════════════════════════════
    // RÉSERVATIONS
    // ═══════════════════════════════════════════════════════════════

    /**
     * POST /api/canadian/reservations
     * Bloque les places pendant le délai de paiement (15 minutes).
     */
    public function creerReservation(Request $request)
    {
        $passagers = $request->input('passagers', []);
        $nombrePlaces = (int) $request->input('nombre_places', $request->input('nb_places',
            $request->input('nb_passagers', is_array($passagers) && $passagers ? count($passagers) : 1)));

        $validator = Validator::make([
            'vol_id'        => $request->input('vol_id', $request->input('volId')),
            'nombre_places' => $nombrePlaces,
            'passagers'     => $passagers,
        ], [
            'vol_id'        => 'required|integer|exists:vols,id',
            'nombre_places' => 'required|integer|min:1|max:9',
            'passagers'     => 'nullable|array|max:9',
        ], [
            'vol_id.required'   => 'Le vol est obligatoire.',
            'vol_id.exists'     => 'Le vol sélectionné n\'existe pas.',
            'nombre_places.min' => 'Il faut au moins 1 place.',
            'nombre_places.max' => 'Une réservation est limitée à 9 passagers.',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        try {
            $reservation = $this->reservations->reserver(
                $request->user(),
                (int) $request->input('vol_id', $request->input('volId')),
                $nombrePlaces,
                is_array($passagers) ? $passagers : [],
            );
        } catch (ReservationImpossibleException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], $e->statutHttp());
        }

        return response()->json([
            'success'     => true,
            'message'     => 'Places réservées : finalisez le paiement dans les ' . ReservationVolService::DELAI_MINUTES . ' minutes.',
            'reservation' => $this->presenter($reservation->load('vol.partenaire')),
            'reference'   => $reservation->reference,
        ], 201);
    }

    /** GET /api/canadian/mes-reservations */
    public function mesReservations(Request $request)
    {
        $this->reservations->expirer();

        $reservations = Reservation::with('vol.partenaire.logoMedia')
            ->where('canadian_user_id', $request->user()->id)
            ->latest()
            ->get()
            ->map(fn ($r) => $this->presenter($r))
            ->values();

        return response()->json(['success' => true, 'reservations' => $reservations]);
    }

    /** GET /api/canadian/reservations/{id} */
    public function showReservation(Request $request, $id)
    {
        $reservation = $this->reservationDuClient($request, $id);
        if (! $reservation) {
            return response()->json(['success' => false, 'message' => 'Réservation introuvable.'], 404);
        }

        return response()->json(['success' => true, 'reservation' => $this->presenter($reservation, true)]);
    }

    /** POST /api/canadian/reservations/{id}/annuler : le client renonce avant de payer */
    public function annulerReservation(Request $request, $id)
    {
        $reservation = $this->reservationDuClient($request, $id);
        if (! $reservation) {
            return response()->json(['success' => false, 'message' => 'Réservation introuvable.'], 404);
        }
        if ($reservation->statut !== 'En_Attente') {
            return response()->json([
                'success' => false,
                'message' => 'Seule une réservation non payée peut être annulée ici. Pour une réservation payée, contactez la compagnie.',
            ], 409);
        }

        // Un paiement peut avoir été validé sur le téléphone entre-temps
        if ($reservation->reference_paiement) {
            app(PaiementVolController::class)->actualiser($reservation);
            $reservation->refresh();
            if ($reservation->statut !== 'En_Attente') {
                return response()->json(['success' => false, 'message' => 'Le paiement de cette réservation vient d\'être validé : elle ne peut plus être annulée ici.'], 409);
            }
        }

        $this->reservations->liberer($reservation, 'Annulee');
        Audit::log('reservation.annulee_client', $reservation, ['reference' => $reservation->reference], $reservation->agence_id);

        return response()->json(['success' => true, 'message' => 'Réservation annulée.', 'reservation' => $this->presenter($reservation->fresh(['vol.partenaire']))]);
    }

    /** GET /api/canadian/reservations/{id}/billet : billet électronique (réservation payée) */
    public function billet(Request $request, $id)
    {
        $reservation = $this->reservationDuClient($request, $id);
        if (! $reservation) {
            return response()->json(['success' => false, 'message' => 'Réservation introuvable.'], 404);
        }
        if (! in_array($reservation->statut, ['Paye', 'Confirmee'], true)) {
            return response()->json(['success' => false, 'message' => 'Le billet est disponible une fois le paiement confirmé.'], 409);
        }

        return response()->json(['success' => true, 'billet' => $this->presenter($reservation, true)]);
    }

    // ═══════════════════════════════════════════════════════════════
    // NOTIFICATIONS
    // ═══════════════════════════════════════════════════════════════

    public function notifications(Request $request)
    {
        $notifs = Notification::where('canadian_user_id', $request->user()->id)
            ->latest()
            ->limit(50)
            ->get()
            ->map(fn ($n) => [
                'id'      => $n->id,
                'titre'   => $n->titre ?? 'Notification',
                'message' => $n->message ?? '',
                'type'    => $n->type ?? 'info',
                'date'    => $n->created_at ? Carbon::parse($n->created_at)->locale('fr')->diffForHumans() : '',
                'lu'      => (bool) $n->lu,
            ])
            ->values();

        return response()->json(['success' => true, 'notifications' => $notifs]);
    }

    public function marquerNotificationLue(Request $request, $id)
    {
        $modifiees = Notification::where('id', $id)
            ->where('canadian_user_id', $request->user()->id)
            ->update(['lu' => true]);

        return $modifiees
            ? response()->json(['success' => true, 'message' => 'Notification marquée comme lue.'])
            : response()->json(['success' => false, 'message' => 'Notification introuvable.'], 404);
    }

    // ═══════════════════════════════════════════════════════════════
    // PROFIL ET COMPTE
    // ═══════════════════════════════════════════════════════════════

    public function profile(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'user'    => [
                'id'              => $user->id,
                'nom'             => $user->nom ?? $user->name,
                'prenom'          => $user->prenom ?? '',
                'email'           => str_ends_with((string) $user->email, '@canadian.local') ? null : $user->email,
                'telephone'       => $user->telephone,
                'compte_confirme' => (bool) $user->compte_confirme,
            ],
        ]);
    }

    /** DELETE /api/canadian/compte : suppression par le client (mot de passe redemandé) */
    public function supprimerCompte(Request $request)
    {
        $user = $request->user();
        $request->validate(['mot_de_passe' => 'required|string'], ['mot_de_passe.required' => 'Saisissez votre mot de passe pour confirmer.']);

        if (! Hash::check($request->mot_de_passe, $user->password)) {
            return response()->json(['message' => 'Mot de passe incorrect.'], 422);
        }

        \App\Support\SuppressionCompte::canadien($user);

        return response()->json(['message' => 'Votre compte a été supprimé.']);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['success' => true, 'message' => 'Déconnexion réussie.']);
    }

    // ═══════════════════════════════════════════════════════════════

    private function reservationDuClient(Request $request, $id): ?Reservation
    {
        $this->reservations->expirer();

        return Reservation::with(['vol.partenaire.logoMedia', 'passagers'])
            ->where('id', $id)
            ->where('canadian_user_id', $request->user()->id)
            ->first();
    }

    /** Réservation telle que l'application l'affiche. */
    private function presenter(Reservation $r, bool $detail = false): array
    {
        $paye = in_array($r->statut, ['Paye', 'Confirmee'], true);
        $reference = $r->reference ?? ('R-' . str_pad($r->id, 6, '0', STR_PAD_LEFT));

        $donnees = [
            'id'               => $r->id,
            'reference'        => $reference,
            'code_reservation' => $reference,
            'codeReservation'  => $reference,
            // Le PNR n'est communiqué qu'une fois payé
            'code_pnr'         => $paye ? $r->code_pnr : null,
            'vol_id'           => $r->vol_id,
            'nombre_places'    => (int) $r->nombre_places,
            'prix_total'       => (float) $r->montant_total,
            'montant_total'    => (float) $r->montant_total,
            'statut'           => $r->statut,
            'statut_libelle'   => self::libelleStatut($r->statut),
            'expire_le'        => $r->statut === 'En_Attente' ? $r->expire_le?->toIso8601String() : null,
            'date_reservation' => $r->created_at?->toIso8601String(),
            'date_paiement'    => $r->date_paiement?->toIso8601String(),
            'methode_paiement' => $r->methode_paiement,
            'vol'              => $r->vol ? FormatVol::pourMobile($r->vol) : null,
        ];

        if ($detail) {
            $donnees['passagers'] = $r->passagers->map(fn ($p) => [
                'nom' => $p->nom, 'prenom' => $p->prenom, 'type' => $p->type,
            ])->values();
        }

        return $donnees;
    }

    public static function libelleStatut(?string $statut): string
    {
        return match ($statut) {
            'En_Attente'   => 'En attente de paiement',
            'Paye'         => 'Payée',
            'Confirmee'    => 'Confirmée',
            'Annulee'      => 'Annulée',
            'Expiree'      => 'Expirée (non payée)',
            'Echouee'      => 'Paiement échoué',
            'A_Rembourser' => 'Remboursement en cours',
            'Remboursee'   => 'Remboursée',
            default        => (string) $statut,
        };
    }
}
