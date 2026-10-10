<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vol;
use App\Models\Reservation;
use App\Models\Notification;
use App\Models\Paiement;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CanadianClientController extends Controller
{
    // ═══════════════════════════════════════════════════════════════
    // VILLES + VOLS (accueil)
    // ═══════════════════════════════════════════════════════════════
    public function villesVols()
    {
        try {
            $departs  = Vol::distinct()->pluck('ville_depart')->filter();
            $arrivees = Vol::distinct()->pluck('ville_arrivee')->filter();

            $villes = $departs->merge($arrivees)
                ->unique()
                ->sort()
                ->values();

            $vols = Vol::whereIn('statut', ['programme', 'en_vol'])
                ->orderBy('date_depart')
                ->limit(20)
                ->get()
                ->map(fn ($v) => $this->formatVol($v))
                ->values();

            return response()->json([
                'success' => true,
                'villes'  => $villes,
                'vols'    => $vols,
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur : ' . $e->getMessage(),
                'villes'  => [],
                'vols'    => [],
            ], 200);
        }
    }

    // ═══════════════════════════════════════════════════════════════
    // RECHERCHE DE VOLS
    // POST /api/canadian/recherche-vols
    // ═══════════════════════════════════════════════════════════════
    public function rechercheVols(Request $request)
    {
        $villeDepart  = $request->input('ville_depart',
                            $request->input('depart',
                                $request->input('origine', '')));

        $villeArrivee = $request->input('ville_arrivee',
                            $request->input('arrivee',
                                $request->input('destination', '')));

        $dateDepart   = $request->input('date_depart',
                            $request->input('date', null));

        $validator = Validator::make([
            'ville_depart'  => $villeDepart,
            'ville_arrivee' => $villeArrivee,
            'date_depart'   => $dateDepart,
        ], [
            'ville_depart'  => 'required|string',
            'ville_arrivee' => 'required|string',
            'date_depart'   => 'nullable|date',
        ], [
            'ville_depart.required'  => 'La ville de départ est obligatoire.',
            'ville_arrivee.required' => 'La ville d\'arrivée est obligatoire.',
            'date_depart.date'       => 'La date de départ est invalide.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'vols'    => [],
            ], 422);
        }

        try {
            $query = Vol::where('ville_depart', $villeDepart)
                ->where('ville_arrivee', $villeArrivee)
                ->whereIn('statut', ['programme', 'en_vol']);

            if (!empty($dateDepart)) {
                $query->whereDate('date_depart', $dateDepart);
            }

            $vols = $query->orderBy('date_depart')
                ->get()
                ->map(fn ($v) => $this->formatVol($v))
                ->values();

            return response()->json([
                'success' => true,
                'vols'    => $vols,
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur : ' . $e->getMessage(),
                'vols'    => [],
            ], 200);
        }
    }

    // ═══════════════════════════════════════════════════════════════
    // CRÉER UNE RÉSERVATION
    // POST /api/canadian/reservations
    // ═══════════════════════════════════════════════════════════════
    public function creerReservation(Request $request)
    {
        $nombrePlaces = $request->input('nombre_places',
                            $request->input('nb_places',
                                $request->input('nb_passagers', null)));

        $passagers = $request->input('passagers', []);

        if ($nombrePlaces === null) {
            $nombrePlaces = is_array($passagers) ? count($passagers) : 1;
        }

        $nombrePlaces = (int) $nombrePlaces;

        $validator = Validator::make([
            'vol_id'        => $request->input('vol_id', $request->input('volId')),
            'nombre_places' => $nombrePlaces,
        ], [
            'vol_id'        => 'required|exists:vols,id',
            'nombre_places' => 'required|integer|min:1',
        ], [
            'vol_id.required'        => 'Le vol est obligatoire.',
            'vol_id.exists'          => 'Le vol sélectionné n\'existe pas.',
            'nombre_places.required' => 'Le nombre de places est obligatoire.',
            'nombre_places.integer'  => 'Le nombre de places doit être un nombre entier.',
            'nombre_places.min'      => 'Il faut au moins 1 place.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $vol = Vol::findOrFail($request->input('vol_id', $request->input('volId')));

        if ($vol->places_disponibles < $nombrePlaces) {
            return response()->json([
                'success' => false,
                'message' => 'Plus assez de places disponibles.',
            ], 409);
        }

        DB::beginTransaction();
        try {
            $reference    = 'RES-' . date('Ymd') . '-' . strtoupper(Str::random(6));
            $montantTotal = $vol->prix * $nombrePlaces;

            $reservation = Reservation::create([
                // ⚠️ Contournement temporaire : user_id est obligatoire en base.
                // À retirer une fois la migration 'make_user_id_nullable' exécutée.
                'user_id'          => $request->user()->id,
                'canadian_user_id' => $request->user()->id,
                'vol_id'           => $vol->id,
                'nombre_places'    => $nombrePlaces,
                'montant_total'    => $montantTotal,
                'statut'           => 'En_Attente',
                'reference'        => $reference,
                'type_reservation' => 'aller_simple',
                'classe'           => 'economique',
                'prix_billet'      => $vol->prix,
                'prix_extras'      => 0,
            ]);

            $vol->decrement('places_disponibles', $nombrePlaces);

            Notification::create([
                'canadian_user_id' => $request->user()->id,
                'utilisateur_id'   => null,
                'titre'            => 'Réservation créée',
                'message'          => "Votre réservation #{$reservation->id} a été enregistrée.",
                'type'             => 'success',
                'lu'               => false,
            ]);

            DB::commit();

            return response()->json([
                'success'     => true,
                'message'     => 'Réservation créée avec succès.',
                'reservation' => $reservation->load('vol'),
                'reference'   => $reference,
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur : ' . $e->getMessage(),
            ], 500);
        }
    }

    // ═══════════════════════════════════════════════════════════════
    // MES RÉSERVATIONS
    // GET /api/canadian/mes-reservations
    // ═══════════════════════════════════════════════════════════════
    public function mesReservations(Request $request)
    {
        try {
            $user = $request->user();

            $reservations = Reservation::with('vol')
                ->where('canadian_user_id', $user->id)
                ->orderByDesc('created_at')
                ->get()
                ->map(function ($r) {
                    $montantTotal = (float) ($r->montant_total ?? 0);

                    return [
                        'id'               => $r->id,
                        'reference'        => $r->reference ?? ('R-' . str_pad($r->id, 6, '0', STR_PAD_LEFT)),
                        'code_reservation' => $r->reference ?? ('R-' . str_pad($r->id, 6, '0', STR_PAD_LEFT)),
                        'codeReservation'  => $r->reference ?? ('R-' . str_pad($r->id, 6, '0', STR_PAD_LEFT)),
                        'code_pnr'         => $r->code_pnr,
                        'vol_id'           => $r->vol_id,
                        'nombre_places'    => (int) $r->nombre_places,
                        'prix_total'       => $montantTotal,
                        'montant_total'    => $montantTotal,
                        'statut'           => $r->statut,
                        'date_reservation' => $r->created_at ? $r->created_at->toIso8601String() : null,
                        'vol'              => $r->vol ? $this->formatVol($r->vol) : null,
                    ];
                })
                ->values();

            return response()->json([
                'success'      => true,
                'reservations' => $reservations,
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'success'      => false,
                'message'      => 'Erreur : ' . $e->getMessage(),
                'reservations' => [],
            ], 200);
        }
    }

    // ═══════════════════════════════════════════════════════════════
    // INITIER UN PAIEMENT  (⭐ CORRIGÉ POUR LA VRAIE TABLE paiements)
    // POST /api/canadian/paiements/initier
    // ═══════════════════════════════════════════════════════════════
    public function initierPaiement(Request $request)
    {
        $reservationId = $request->input('reservation_id',
                            $request->input('reservationId', null));

        $methode = $request->input('methode',
                        $request->input('methode_paiement', 'mtn_momo'));

        // ⭐ Mapping des méthodes Flutter → valeurs Admin
        $methodeMap = [
            'mtn_momo'     => 'MTN',
            'airtel_money' => 'Airtel',
            'carte'        => 'Especes',
            'mobile_money' => 'MTN',
            'virement'     => 'Especes',
        ];
        $methodeAdmin = $methodeMap[$methode] ?? 'MTN';

        $validator = Validator::make([
            'reservation_id' => $reservationId,
        ], [
            'reservation_id' => 'required|exists:reservations,id',
        ], [
            'reservation_id.required' => 'La réservation est obligatoire.',
            'reservation_id.exists'   => 'La réservation n\'existe pas.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $reservation = Reservation::with('vol')
            ->where('id', $reservationId)
            ->where('canadian_user_id', $request->user()->id)
            ->first();

        if (!$reservation) {
            return response()->json([
                'success' => false,
                'message' => 'Réservation introuvable.',
            ], 404);
        }

        DB::beginTransaction();
        try {
            $user      = $request->user();
            $reference = 'PAY-' . strtoupper(uniqid());
            $montant   = (float) ($reservation->montant_total ?? 0);
            $nomClient = trim(($user->nom ?? '') . ' ' . ($user->prenom ?? ''));

            // ⭐ 1. Créer le paiement (vraie structure de ta table)
            Paiement::create([
                'reservation_id'       => $reservation->id,
                'user_id'              => $user->id,
                'reference'            => $reference,
                'montant'              => $montant,
                'devise'               => 'XAF',
                'mode_paiement'        => $methodeAdmin,
                'telephone_paiement'   => $user->telephone ?? '',
                'statut'               => 'Reussi',
                'date_paiement'        => now(),
                'details'              => [
                    'nom_client'   => $nomClient,
                    'vol_numero'   => $reservation->vol->numero_vol ?? null,
                    'nombre_places'=> $reservation->nombre_places,
                ],
            ]);

            // ⭐ 2. Mettre à jour la réservation
            $reservation->update([
                'statut'             => 'Paye',
                'date_paiement'      => now(),
                'methode_paiement'   => $methodeAdmin,
                'reference_paiement' => $reference,
                'code_reservation'   => 'BK-' . str_pad($reservation->id, 6, '0', STR_PAD_LEFT),
                'code_pnr'           => strtoupper(Str::random(6)),
            ]);

            // ⭐ 3. Notification client
            Notification::create([
                'canadian_user_id' => $user->id,
                'utilisateur_id'   => null,
                'titre'            => 'Paiement confirmé',
                'message'          => "Votre paiement de {$montant} FCFA a été confirmé. Référence : {$reference}",
                'type'             => 'success',
                'lu'               => false,
            ]);

            DB::commit();

            return response()->json([
                'success'        => true,
                'message'        => 'Paiement effectué avec succès.',
                'reference'      => $reference,
                'montant'        => $montant,
                'reservation_id' => $reservation->id,
                'methode'        => $methodeAdmin,
            ], 200);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur : ' . $e->getMessage(),
            ], 500);
        }
    }

    // ═══════════════════════════════════════════════════════════════
    // MES NOTIFICATIONS
    // ═══════════════════════════════════════════════════════════════
    public function notifications(Request $request)
    {
        try {
            $user = $request->user();

            $notifs = Notification::where('canadian_user_id', $user->id)
                ->orderByDesc('created_at')
                ->limit(50)
                ->get()
                ->map(function ($n) {
                    return [
                        'id'      => $n->id,
                        'titre'   => $n->titre ?? 'Notification',
                        'message' => $n->message ?? '',
                        'type'    => $n->type ?? 'info',
                        'date'    => $n->created_at
                            ? Carbon::parse($n->created_at)->diffForHumans()
                            : '',
                        'lu'      => (bool) ($n->lu ?? false),
                    ];
                })
                ->values();

            return response()->json([
                'success'       => true,
                'notifications' => $notifs,
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'success'       => true,
                'notifications' => [],
            ], 200);
        }
    }

    // ═══════════════════════════════════════════════════════════════
    // PROFIL
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
                'email'           => $user->email,
                'telephone'       => $user->telephone,
                'compte_confirme' => (bool) ($user->compte_confirme ?? false),
            ],
        ], 200);
    }

    // ═══════════════════════════════════════════════════════════════
    // LOGOUT
    // ═══════════════════════════════════════════════════════════════
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Déconnexion réussie.',
        ], 200);
    }

    // ═══════════════════════════════════════════════════════════════
    // ⭐ DÉTAIL D'UNE RÉSERVATION
    // GET /api/canadian/reservations/{id}
    // ═══════════════════════════════════════════════════════════════
    public function showReservation(Request $request, $id)
    {
        try {
            $reservation = Reservation::with('vol')
                ->where('id', $id)
                ->where('canadian_user_id', $request->user()->id)
                ->first();

            if (!$reservation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Réservation introuvable.',
                ], 404);
            }

            return response()->json([
                'success'     => true,
                'reservation' => [
                    'id'               => $reservation->id,
                    'reference'        => $reservation->reference ?? ('R-' . str_pad($reservation->id, 6, '0', STR_PAD_LEFT)),
                    'code_pnr'         => $reservation->code_pnr,
                    'nombre_places'    => (int) $reservation->nombre_places,
                    'montant_total'    => (float) ($reservation->montant_total ?? 0),
                    'statut'           => $reservation->statut,
                    'date_reservation' => $reservation->created_at ? $reservation->created_at->toIso8601String() : null,
                    'vol'              => $reservation->vol ? $this->formatVol($reservation->vol) : null,
                ],
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur : ' . $e->getMessage(),
            ], 500);
        }
    }

    // ═══════════════════════════════════════════════════════════════
    // ⭐ LISTE DES EXTRAS DISPONIBLES
    // GET /api/canadian/extras
    // ═══════════════════════════════════════════════════════════════
    public function listExtras(Request $request)
    {
        try {
            $extras = \App\Models\Extra::orderBy('ordre')
                ->get()
                ->map(fn($e) => [
                    'id'          => $e->id,
                    'nom'         => $e->nom,
                    'description' => $e->description,
                    'prix'        => (float) ($e->prix ?? 0),
                    'icone'       => $e->icone,
                ])
                ->values();

            return response()->json([
                'success' => true,
                'extras'  => $extras,
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'extras'  => [],
                'message' => 'Erreur : ' . $e->getMessage(),
            ], 200);
        }
    }

    // ═══════════════════════════════════════════════════════════════
    // ⭐ MARQUER UNE NOTIFICATION COMME LUE
    // PATCH /api/canadian/notifications/{id}/lu
    // ═══════════════════════════════════════════════════════════════
    public function marquerNotificationLue(Request $request, $id)
    {
        try {
            $notif = \App\Models\Notification::where('id', $id)
                ->where('canadian_user_id', $request->user()->id)
                ->first();

            if (!$notif) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notification introuvable.',
                ], 404);
            }

            $notif->update(['lu' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Notification marquée comme lue.',
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur : ' . $e->getMessage(),
            ], 500);
        }
    }

    // ═══════════════════════════════════════════════════════════════
    // ⭐ FORMATAGE D'UN VOL POUR FLUTTER
    // ═══════════════════════════════════════════════════════════════
    private function formatVol($vol): array
    {
        if (!$vol || !is_object($vol)) {
            return [];
        }

        $dateDepart  = !empty($vol->date_depart)  ? Carbon::parse($vol->date_depart)  : null;
        $dateArrivee = !empty($vol->date_arrivee) ? Carbon::parse($vol->date_arrivee) : null;

        $duree = '';
        if ($dateDepart && $dateArrivee) {
            $diff   = $dateDepart->diff($dateArrivee);
            $heures = $diff->h + ($diff->d * 24);
            $duree  = sprintf('%dh%02d', $heures, $diff->i);
        }

        $dateFr = $dateDepart
            ? $dateDepart->locale('fr')->isoFormat('dddd D MMMM YYYY')
            : '';

        return [
            'id'                 => $vol->id ?? null,
            'numero_vol'         => $vol->numero_vol ?? '',
            'numeroVol'          => $vol->numero_vol ?? '',

            'ville_depart'       => $vol->ville_depart ?? '',
            'villeDepart'        => $vol->ville_depart ?? ($vol->origine ?? ''),
            'ville_arrivee'      => $vol->ville_arrivee ?? '',
            'villeArrivee'       => $vol->ville_arrivee ?? ($vol->destination ?? ''),

            'origine'            => $vol->origine ?? '',
            'destination'        => $vol->destination ?? '',

            'heure_depart'       => $dateDepart  ? $dateDepart->format('H:i')  : '',
            'heureDepart'        => $dateDepart  ? $dateDepart->format('H:i')  : '',
            'heure_arrivee'      => $dateArrivee ? $dateArrivee->format('H:i') : '',
            'heureArrivee'       => $dateArrivee ? $dateArrivee->format('H:i') : '',

            'date_depart'        => $dateDepart  ? $dateDepart->format('Y-m-d H:i:s')  : null,
            'date_arrivee'       => $dateArrivee ? $dateArrivee->format('Y-m-d H:i:s') : null,
            'date_formatee'      => $dateFr,
            'dateFormatee'       => $dateFr,

            'duree_formatee'     => $duree,
            'dureeFormatee'      => $duree,

            'prix'               => (float) ($vol->prix ?? 0),
            'prix_base'          => (float) ($vol->prix ?? 0),
            'prixBase'           => (float) ($vol->prix ?? 0),

            'places_disponibles' => (int) ($vol->places_disponibles ?? 0),
            'placesRestantes'    => (int) ($vol->places_disponibles ?? 0),

            'statut'             => $vol->statut ?? 'programme',
            'type_vol'           => $vol->type_vol ?? 'direct',
            'typeVol'            => $vol->type_vol ?? 'direct',
            'compagnie'          => $vol->compagnie ?? 'Canadian Airways',
            'avion'              => $vol->avion ?? null,
        ];
    }
}