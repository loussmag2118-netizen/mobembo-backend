<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PlanificationVoyage;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AirtelMoneyController extends Controller
{
    /**
     * ⭐ MODE SIMULATION
     */
    private function isSimulation(): bool
    {
        return empty(config('airtel.client_id'))
            || empty(config('airtel.client_secret'))
            || env('SIMULATION_MODE', false) === true;
    }

    /**
     * Initier un paiement Airtel Money (ou simulation).
     */
    public function requestToPay(Request $request)
    {
        // ============================================================
        // 1. VALIDATION
        // ============================================================
        $validator = Validator::make($request->all(), [
            'amount'                  => 'required|numeric|min:100',
            'phone'                   => ['required', 'string', 'regex:/^(\+242|242)?05[0-9]{7}$/'],
            'planification_voyage_id' => 'required|exists:planification_voyages,id',
            'passagers'               => 'required|array|min:1',
            'passagers.*.nom'         => 'required|string|max:150',
            'passagers.*.prenom'      => 'nullable|string|max:150',
            'passagers.*.telephone'   => 'nullable|string|max:30',
        ], [
            'phone.regex' => 'Le numéro Airtel doit commencer par 05 (ex: 05XXXXXXX).',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        // ============================================================
        // 2. MODE SIMULATION
        // ============================================================
        if ($this->isSimulation()) {
            return $this->creerTickets(
                $request,
                'Airtel',
                'AIRTEL-SIM-' . strtoupper(Str::random(10)),
                true
            );
        }

        // ============================================================
        // 3. MODE RÉEL — Authentification Airtel
        // ============================================================
        $accessToken = $this->getAirtelAccessToken();

        if (!$accessToken) {
            return response()->json([
                'message' => 'Impossible de s\'authentifier auprès du service Airtel Money.',
            ], 500);
        }

        $referenceId = 'AIRTEL-' . strtoupper(Str::uuid());

        // ============================================================
        // 4. APPEL API AIRTEL (à adapter selon la doc officielle)
        // ============================================================
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type'  => 'application/json',
                'X-Country'     => 'CG',
                'X-Currency'    => 'XAF',
            ])->post(config('airtel.base_url') . '/merchant/v1/payments/', [
                'reference' => $referenceId,
                'subscriber' => [
                    'country'  => 'CG',
                    'currency' => 'XAF',
                    'msisdn'   => $request->phone,
                ],
                'transaction' => [
                    'amount'   => (int) $request->amount,
                    'country'  => 'CG',
                    'currency' => 'XAF',
                    'id'       => $referenceId,
                ],
            ]);

            if (in_array($response->status(), [200, 201, 202])) {
                return $this->creerTickets($request, 'Airtel', $referenceId, false);
            }

            return response()->json([
                'message' => 'Échec initiation Airtel.',
                'details' => $response->json(),
            ], $response->status());

        } catch (\Exception $e) {
            Log::error('Erreur Airtel : ' . $e->getMessage());
            return response()->json([
                'message' => 'Erreur lors de la communication avec Airtel.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Récupère un access token Airtel.
     */
    private function getAirtelAccessToken(): ?string
    {
        try {
            $response = Http::post(config('airtel.base_url') . '/auth/oauth2/token', [
                'client_id'     => config('airtel.client_id'),
                'client_secret' => config('airtel.client_secret'),
                'grant_type'    => 'client_credentials',
            ]);

            if ($response->successful()) {
                return $response->json()['access_token'] ?? null;
            }

            Log::error('Erreur Token Airtel : ' . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error('Exception Token Airtel : ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Crée les tickets en base.
     */
    private function creerTickets(Request $request, string $mode, string $referenceId, bool $simulation = false)
    {
        try {
            $voyage = PlanificationVoyage::with([
                'trajet.gareArrivee',
                'trajet.gareDepart',
                'vagueDepart',
                'bus',
            ])->findOrFail($request->planification_voyage_id);

            $gareArrivee = $voyage->trajet->gareArrivee ?? null;
            $gareDepart  = $voyage->trajet->gareDepart  ?? null;
            $vague       = $voyage->vagueDepart;

            $nombrePassagers  = count($request->input('passagers', []));
            $montantParTicket = $nombrePassagers > 0
                ? $request->amount / $nombrePassagers
                : $request->amount;

            foreach ($request->input('passagers', []) as $passager) {
                Ticket::create([
                    'planification_voyage_id' => $voyage->id,
                    'agence_id'               => $voyage->agence_id,
                    'agent_vendeur_id'        => null,
                    'agent_controleur_id'     => null,

                    'reference_ticket' => 'TKT-' . $mode . '-' . strtoupper(Str::random(8)),
                    'qr_code_token'    => Str::random(64),

                    'nom_passager'       => $passager['nom'],
                    'prenom_passager'    => $passager['prenom'] ?? '',
                    'telephone_passager' => $passager['telephone'] ?? null,

                    'ville_arrivee'   => $gareArrivee->ville ?? null,
                    'quartier'        => $gareArrivee->quartier ?? $gareArrivee->ville ?? null,
                    'gare_arrivee_id' => $gareArrivee->id ?? null,
                    'numero_bus'      => $voyage->bus->numero ?? null,
                    'nom_trajet'      => ($gareDepart->nom_gare ?? '?') . ' → ' . ($gareArrivee->nom_gare ?? '?'),

                    'horaire'   => $vague->heure_depart ?? null,
                    'nom_vague' => $vague->nom_vague ?? null,

                    'nombre_places_reservees' => 1,
                    'montant_total'           => $montantParTicket,

                    'statut_paiement'     => 'Paye',
                    'statut_embarquement' => 'En_Attente',

                    'mode_paiement' => $mode,
                    'date_paiement' => now(),
                    'date_reservation' => now(),
                ]);
            }

            $voyage->decrement('places_disponibles', $nombrePassagers);

            return response()->json([
                'message'      => $simulation
                    ? 'Paiement Airtel simulé avec succès.'
                    : 'Demande Airtel envoyée. Validez sur votre téléphone.',
                'reference_id' => $referenceId,
                'status'       => $simulation ? 'PAID' : 'PENDING',
                'simulation'   => $simulation,
            ], 200);

        } catch (\Exception $e) {
            Log::error("Erreur création tickets $mode : " . $e->getMessage());
            return response()->json([
                'message' => 'Erreur lors de la création des billets.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}