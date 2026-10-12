<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PlanificationVoyage;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class MomoController extends Controller
{
    private function isSimulation(): bool
    {
        return empty(config('momo.collection.api_user'))
            || empty(config('momo.collection.api_key'))
            || env('SIMULATION_MODE', false) === true
            || config('momo.simulation', false) === true;
    }

    private function getAccessToken(): ?string
    {
        $apiUser    = config('momo.collection.api_user');
        $apiKey     = config('momo.collection.api_key');
        $primaryKey = config('momo.collection.primary_key');

        try {
            $response = Http::withHeaders([
                'Ocp-Apim-Subscription-Key' => $primaryKey,
                'Authorization'             => 'Basic ' . base64_encode($apiUser . ':' . $apiKey),
            ])->post(config('momo.urls.sandbox.token'));

            if ($response->successful()) {
                return $response->json()['access_token'] ?? null;
            }

            Log::error('Erreur Token MTN : ' . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error('Exception Token MTN : ' . $e->getMessage());
            return null;
        }
    }

    public function requestToPay(Request $request)
    {
        // 🆕 Montant minimum configurable via .env (MOMO_MIN_AMOUNT)
        $minAmount = (int) config('momo.min_amount', 100);

        $validator = Validator::make($request->all(), [
            'amount'                  => 'required|numeric|min:' . $minAmount,
            'phone'                   => ['required', 'string', 'regex:/^(\+242|242)?06[0-9]{7}$/'],
            'planification_voyage_id' => 'required|exists:planification_voyages,id',
            'passagers'               => 'required|array|min:1',
            'passagers.*.nom'         => 'required|string|max:150',
            'passagers.*.prenom'      => 'nullable|string|max:150',
            'passagers.*.telephone'   => 'nullable|string|max:30',
        ], [
            'phone.regex'  => 'Le numéro MTN doit commencer par 06 (ex: 06XXXXXXX).',
            'amount.min'   => "Le champ amount doit être au moins à {$minAmount}.",
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        // 🆕 Vérification que le montant envoyé correspond au prix réel du voyage
        $voyage = PlanificationVoyage::find($request->planification_voyage_id);

        if (!$voyage) {
            return response()->json([
                'message' => 'Voyage introuvable.',
            ], 404);
        }

        $nombrePassagers = count($request->input('passagers', []));
        $montantAttendu  = (float) $voyage->prix * $nombrePassagers;
        $montantRecu     = (float) $request->amount;

        if (abs($montantAttendu - $montantRecu) > 0.01) {
            return response()->json([
                'message' => 'Le montant envoyé ne correspond pas au prix du voyage.',
                'attendu' => $montantAttendu,
                'recu'    => $montantRecu,
            ], 422);
        }

        if ($this->isSimulation()) {
            return $this->simulerPaiement($request);
        }

        $primaryKey  = config('momo.collection.primary_key');
        $accessToken = $this->getAccessToken();

        if (!$accessToken) {
            return response()->json([
                'message' => 'Impossible de s\'authentifier auprès du service MTN MoMo.',
            ], 500);
        }

        $referenceId = (string) Str::uuid();
        $externalId  = 'Mobembo-' . time() . '-' . rand(1000, 9999);
        $targetEnv   = config('momo.collection.target_environment', 'sandbox');

        $response = Http::withHeaders([
            'Authorization'             => 'Bearer ' . $accessToken,
            'X-Reference-Id'            => $referenceId,
            'X-Target-Environment'      => $targetEnv,
            'Ocp-Apim-Subscription-Key' => $primaryKey,
            'Content-Type'              => 'application/json',
        ])->post(config('momo.urls.sandbox.pay'), [
            'amount'       => (string) $request->amount,
            'currency'     => 'XAF',
            'externalId'   => $externalId,
            'payer'        => [
                'partyIdType' => 'MSISDN',
                'partyId'     => $request->phone,
            ],
            'payerMessage' => 'Mobembo Platform',
            'payeeNote'    => 'Paiement de ticket via Mobembo',
        ]);

        DB::table('paiements_mtn_momo')->insert([
            'reference_id'   => $referenceId,
            'external_id'    => $externalId,
            'amount'         => $request->amount,
            'currency'       => 'XAF',
            'payer_msisdn'   => $request->phone,
            'payer_message'  => 'Mobembo Platform',
            'payee_note'     => 'Paiement de ticket via Mobembo',
            'status'         => $response->status() === 202 ? 'PENDING' : 'FAILED',
            'mtn_response'   => json_encode($response->json()),
            'environment'    => $targetEnv,
            'simulation'     => false,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        if ($response->status() === 202) {
            return $this->creerTickets($request, 'MTN', $referenceId);
        }

        return response()->json([
            'message' => 'Échec initiation MTN.',
            'details' => $response->json(),
        ], $response->status());
    }

    private function simulerPaiement(Request $request)
    {
        $referenceId = 'SIM-' . strtoupper(Str::random(10));

        DB::table('paiements_mtn_momo')->insert([
            'reference_id' => $referenceId,
            'external_id'  => 'SIM-EXT-' . rand(1000, 9999),
            'amount'       => $request->amount,
            'currency'     => 'XAF',
            'payer_msisdn' => $request->phone,
            'status'       => 'SUCCESSFUL',
            'environment'  => 'sandbox',
            'simulation'   => true,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        return $this->creerTickets($request, 'MTN', $referenceId, true);
    }

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

            $nombrePassagers = count($request->input('passagers', []));
            $prixUnitaire    = (float) ($voyage->prix ?? 0);

            $ticketsCrees = [];
            foreach ($request->input('passagers', []) as $passager) {
                $ticket = Ticket::create([
                    'planification_voyage_id' => $voyage->id,
                    'agence_id'               => $voyage->agence_id,
                    'reference_ticket'        => 'TKT-' . $mode . '-' . strtoupper(Str::random(8)),
                    'qr_code_token'           => Str::random(64),
                    'nom_passager'            => $passager['nom'],
                    'prenom_passager'         => $passager['prenom'] ?? '',
                    'telephone_passager'      => $passager['telephone'] ?? null,
                    'ville_arrivee'           => $gareArrivee->ville ?? null,
                    'quartier'                => $gareArrivee->quartier ?? $gareArrivee->ville ?? null,
                    'gare_arrivee_id'         => $gareArrivee->id ?? null,
                    'numero_bus'              => $voyage->bus->numero ?? null,
                    'nom_trajet'              => ($gareDepart->nom_gare ?? '?') . ' → ' . ($gareArrivee->nom_gare ?? '?'),
                    'horaire'                 => $vague->heure_depart ?? null,
                    'nom_vague'               => $vague->nom_vague ?? null,
                    'nombre_places_reservees' => 1,
                    'montant_total'           => $prixUnitaire,
                    'statut_paiement'         => $simulation ? 'Paye' : 'En_Attente',
                    'statut_embarquement'     => 'En_Attente',
                    'mode_paiement'           => $mode,
                    'date_paiement'           => $simulation ? now() : null,
                    'date_reservation'        => now(),
                ]);

                $ticketsCrees[] = [
                    'id'               => $ticket->id,
                    'reference_ticket' => $ticket->reference_ticket,
                    'reference'        => $ticket->reference_ticket,
                    'qr_code_token'    => $ticket->qr_code_token,
                    'nom'              => $ticket->nom_passager,
                    'prenom'           => $ticket->prenom_passager,
                    'telephone'        => $ticket->telephone_passager,
                    'nom_trajet'       => $ticket->nom_trajet,
                    'gareDepart'       => $gareDepart->nom_gare ?? '',
                    'gareArrivee'      => $gareArrivee->nom_gare ?? '',
                    'villeDepart'      => $gareDepart->ville ?? '',
                    'villeArrivee'     => $gareArrivee->ville ?? '',
                    'horaire'          => $ticket->horaire,
                    'date'             => $voyage->date_voyage ? $voyage->date_voyage->format('d/m/Y') : null,
                    'numeroBus'        => $ticket->numero_bus,
                    'prix'             => (string) $prixUnitaire,
                ];
            }

            $voyage->decrement('places_disponibles', $nombrePassagers);

            return response()->json([
                'success'      => true,
                'message'      => $simulation
                    ? 'Paiement simulé avec succès.'
                    : 'Demande MTN envoyée. Validez sur votre téléphone.',
                'reference_id' => $referenceId,
                'status'       => $simulation ? 'PAID' : 'PENDING',
                'simulation'   => $simulation,
                'tickets'      => $ticketsCrees,
            ], 200);

        } catch (\Exception $e) {
            Log::error("Erreur création tickets $mode : " . $e->getMessage());
            return response()->json([
                'message' => 'Erreur lors de la création des billets.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function checkTransactionStatus($referenceId)
    {
        if ($this->isSimulation()) {
            return response()->json([
                'transaction' => ['status' => 'SUCCESSFUL'],
                'simulation'  => true,
            ], 200);
        }

        $primaryKey  = config('momo.collection.primary_key');
        $accessToken = $this->getAccessToken();

        if (!$accessToken) {
            return response()->json(['message' => 'Erreur d\'authentification MoMo.'], 500);
        }

        $targetEnv = config('momo.collection.target_environment', 'sandbox');

        $response = Http::withHeaders([
            'Authorization'             => 'Bearer ' . $accessToken,
            'X-Target-Environment'      => $targetEnv,
            'Ocp-Apim-Subscription-Key' => $primaryKey,
        ])->get("https://sandbox.momodeveloper.mtn.com/collection/v1_0/requesttopay/{$referenceId}");

        if ($response->successful()) {
            $data   = $response->json();
            $status = $data['status'] ?? 'PENDING';

            DB::table('paiements_mtn_momo')
                ->where('reference_id', $referenceId)
                ->update([
                    'status'                   => $status,
                    'financial_transaction_id' => $data['financialTransactionId'] ?? null,
                    'mtn_response'             => json_encode($data),
                    'updated_at'               => now(),
                ]);

            return response()->json(['transaction' => $data], 200);
        }

        return response()->json([
            'message' => 'Impossible de récupérer le statut.',
        ], $response->status());
    }
}