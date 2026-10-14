<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\ReservationImpossibleException;
use App\Http\Controllers\Controller;
use App\Models\PlanificationVoyage;
use App\Models\Ticket;
use App\Services\PaiementService;
use App\Services\ReservationService;
use App\Support\Audit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

/**
 * Achat de billets de bus : paiement MTN MoMo / Airtel Money.
 *
 * Déroulé :
 *  1. vérification du montant et des places ;
 *  2. réservation temporaire des places (ReservationService) ;
 *  3. demande de paiement à l'opérateur ;
 *  4. confirmation par l'opérateur (callback puis vérification de son API,
 *     ou consultation régulière par l'application via /statut).
 */
class PaiementBusController extends Controller
{
    public function __construct(
        private ReservationService $reservations,
        private PaiementService $paiements,
    ) {
    }

    // POST /api/momo/pay
    public function payerMtn(Request $request): JsonResponse
    {
        return $this->payer($request, PaiementService::MTN, '/^(\+242|242)?06[0-9]{7}$/',
            'Le numéro MTN doit commencer par 06 (ex: 06XXXXXXX).');
    }

    // POST /api/airtel/pay
    public function payerAirtel(Request $request): JsonResponse
    {
        return $this->payer($request, PaiementService::AIRTEL, '/^(\+242|242)?05[0-9]{7}$/',
            'Le numéro Airtel doit commencer par 05 (ex: 05XXXXXXX).');
    }

    private function payer(Request $request, string $operateur, string $regexTelephone, string $messageTelephone): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'amount'                  => 'required|numeric|min:' . (int) config('momo.min_amount', 100),
            'phone'                   => ['required', 'string', 'regex:' . $regexTelephone],
            'planification_voyage_id' => 'required|integer|exists:planification_voyages,id',
            'passagers'               => 'required|array|min:1|max:10',
            'passagers.*.nom'         => 'required|string|max:150',
            'passagers.*.prenom'      => 'nullable|string|max:150',
            'passagers.*.telephone'   => 'nullable|string|max:30',
        ], ['phone.regex' => $messageTelephone]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
        }

        // ---- Idempotence : la même demande renvoyée (double appui, réseau
        //      instable…) ne crée pas un second paiement.
        $cle = $request->header('Idempotency-Key') ?: $request->input('cle_idempotence');
        $cleCache = $cle ? 'paiement:idem:' . ($request->user()?->id ?? 'invite') . ':' . sha1($cle) : null;
        if ($cleCache) {
            if (! Cache::add($cleCache, 'EN_COURS', now()->addMinutes((int) config('paiement.idempotence_minutes', 30)))) {
                $existante = Cache::get($cleCache);
                if ($existante && $existante !== 'EN_COURS') {
                    return $this->reponseStatut($existante, 'Paiement déjà enregistré.');
                }

                return response()->json(['message' => 'Un paiement est déjà en cours pour cette demande.'], 409);
            }
        }

        try {
            $reponse = $this->traiterPaiement($request, $operateur);
        } catch (\Throwable $e) {
            if ($cleCache) {
                Cache::forget($cleCache);
            }
            throw $e;
        }

        if ($cleCache) {
            $reference = $reponse->getData(true)['reference_id'] ?? null;
            $reference ? Cache::put($cleCache, $reference, now()->addMinutes((int) config('paiement.idempotence_minutes', 30)))
                       : Cache::forget($cleCache);
        }

        return $reponse;
    }

    private function traiterPaiement(Request $request, string $operateur): JsonResponse
    {
        $voyage    = PlanificationVoyage::findOrFail($request->planification_voyage_id);
        $passagers = $request->input('passagers', []);
        $montant   = (float) $request->amount;

        try {
            $this->reservations->verifierMontant($voyage, count($passagers), $montant);
            $modeTest = $this->paiements->modeTest($operateur);

            // ---------------- MODE TEST (jamais en production sans PAIEMENT_MODE_TEST)
            if ($modeTest) {
                $reference = ($operateur === PaiementService::MTN ? 'SIM-' : 'AIRTEL-SIM-') . strtoupper(Str::random(10));
                $tickets = $this->reservations->reserver(
                    $voyage->id, $passagers, $operateur, $reference, $request->phone,
                    payeImmediatement: true, utilisateurId: $request->user()?->id,
                );
                $this->tracerPaiement($operateur, $voyage, $tickets->first()?->id, $reference, null, $montant,
                    $request->phone, $operateur === PaiementService::MTN ? 'SUCCESSFUL' : 'SUCCESS', null, true);
                Audit::log('paiement.test', $tickets->first(), ['reference' => $reference, 'montant' => $montant]);

                return response()->json([
                    'success'      => true,
                    'message'      => 'Paiement de TEST validé (aucun débit réel).',
                    'reference_id' => $reference,
                    'status'       => 'PAID',
                    'statut'       => PaiementService::PAYE,
                    'simulation'   => true,
                    'tickets'      => $this->reservations->presenterTickets($tickets),
                ]);
            }

            // ---------------- MODE RÉEL
            $jeton = $operateur === PaiementService::MTN ? $this->paiements->jetonMtn() : $this->paiements->jetonAirtel();
            if (! $jeton) {
                return response()->json(['message' => "Le service {$operateur} ne répond pas. Réessayez dans quelques instants."], 502);
            }

            $reference  = $operateur === PaiementService::MTN ? (string) Str::uuid() : 'AIRTEL-' . strtoupper((string) Str::uuid());
            $externalId = 'MBL-' . now()->format('YmdHis') . '-' . random_int(1000, 9999);

            // Les places sont bloquées AVANT la demande de paiement
            $tickets = $this->reservations->reserver(
                $voyage->id, $passagers, $operateur, $reference, $request->phone,
                payeImmediatement: false, utilisateurId: $request->user()?->id,
            );
        } catch (ReservationImpossibleException $e) {
            return response()->json(['message' => $e->getMessage()], $e->statutHttp());
        }

        try {
            $reponse = $operateur === PaiementService::MTN
                ? $this->paiements->demanderMtn($jeton, $reference, $externalId, $montant, $request->phone)
                : $this->paiements->demanderAirtel($jeton, $reference, $montant, $request->phone);
        } catch (\Throwable $e) {
            Log::error("Demande de paiement {$operateur} : " . $e->getMessage());
            $this->reservations->liberer($reference);

            return response()->json(['message' => "Le service {$operateur} ne répond pas. Réessayez."], 502);
        }

        $accepte = $operateur === PaiementService::MTN
            ? $reponse->status() === 202
            : in_array($reponse->status(), [200, 201, 202]) && data_get($reponse->json(), 'status.success', true) !== false;

        $this->tracerPaiement($operateur, $voyage, $tickets->first()?->id, $reference, $externalId, $montant,
            $request->phone, $accepte ? 'PENDING' : 'FAILED', $reponse->json(), false);

        if (! $accepte) {
            $this->reservations->liberer($reference);
            Audit::log('paiement.refuse_initiation', $tickets->first(), ['reference' => $reference, 'http' => $reponse->status()]);

            return response()->json([
                'message' => "Le paiement {$operateur} n'a pas pu être lancé. Vérifiez le numéro et réessayez.",
                'details' => $reponse->json(),
            ], $reponse->status() >= 400 ? $reponse->status() : 502);
        }

        Audit::log('paiement.initie', $tickets->first(), ['reference' => $reference, 'operateur' => $operateur, 'montant' => $montant]);

        return response()->json([
            'success'      => true,
            'message'      => "Demande {$operateur} envoyée. Validez le paiement sur votre téléphone.",
            'reference_id' => $reference,
            'status'       => 'PENDING',
            'statut'       => PaiementService::EN_ATTENTE,
            'simulation'   => false,
            'expire_le'    => $tickets->first()?->expire_le?->toIso8601String(),
            'tickets'      => $this->reservations->presenterTickets($tickets),
        ]);
    }

    // GET /api/paiements/{reference}/statut — consulté régulièrement par l'application
    public function statut(Request $request, string $reference): JsonResponse
    {
        if (! $this->appartientAuClient($request, $reference)) {
            return response()->json(['message' => 'Paiement introuvable.'], 404);
        }

        return $this->reponseStatut($reference);
    }

    // POST /api/paiements/{reference}/annuler — le client renonce avant de payer
    public function annuler(Request $request, string $reference): JsonResponse
    {
        if (! $this->appartientAuClient($request, $reference)) {
            return response()->json(['message' => 'Paiement introuvable.'], 404);
        }

        // On vérifie d'abord auprès de l'opérateur : un paiement déjà validé n'est pas annulé
        $etat = $this->paiements->actualiser($reference);
        if ($etat && $etat['statut'] === PaiementService::EN_ATTENTE) {
            $this->reservations->liberer($reference);
            Audit::log('reservation.annulee_client', $etat['tickets']->first(), ['reference' => $reference]);
        }

        return $this->reponseStatut($reference);
    }

    // POST|PUT /api/paiements/mtn/callback — appelé par MTN
    public function callbackMtn(Request $request): JsonResponse
    {
        // Le contenu du message n'est pas cru sur parole : on retrouve la
        // référence puis on interroge l'API MTN (actualiser).
        $externalId = (string) $request->input('externalId', '');
        $reference  = $request->header('X-Reference-Id')
            ?: DB::table('paiements_mtn_momo')->where('external_id', $externalId)->value('reference_id');

        if ($reference && Ticket::where('reference_paiement', $reference)->exists()) {
            $this->paiements->actualiser($reference);
        } else {
            Log::warning('Callback MTN sans référence connue', ['externalId' => $externalId]);
        }

        return response()->json(['recu' => true]);
    }

    // POST /api/paiements/airtel/callback — appelé par Airtel
    public function callbackAirtel(Request $request): JsonResponse
    {
        $reference = (string) data_get($request->all(), 'transaction.id', '');

        if ($reference !== '' && Ticket::where('reference_paiement', $reference)->exists()) {
            $this->paiements->actualiser($reference);
        } else {
            Log::warning('Callback Airtel sans référence connue', ['reference' => $reference]);
        }

        return response()->json(['recu' => true]);
    }

    // ------------------------------------------------------------------

    private function reponseStatut(string $reference, ?string $message = null): JsonResponse
    {
        $etat = $this->paiements->actualiser($reference);
        if (! $etat) {
            return response()->json(['message' => 'Paiement introuvable.'], 404);
        }

        $messages = [
            PaiementService::PAYE         => 'Paiement confirmé. Vos billets sont prêts.',
            PaiementService::EN_ATTENTE   => 'En attente de votre validation sur le téléphone.',
            PaiementService::ECHOUE       => "Le paiement n'a pas abouti. Aucun billet n'a été émis.",
            PaiementService::A_REMBOURSER => 'Paiement reçu après expiration de la réservation : un remboursement va être effectué.',
        ];

        return response()->json([
            'success'      => $etat['statut'] === PaiementService::PAYE,
            'message'      => $message ?? $messages[$etat['statut']],
            'reference_id' => $reference,
            'statut'       => $etat['statut'],
            'status'       => $etat['statut'] === PaiementService::PAYE ? 'PAID' : ($etat['statut'] === PaiementService::EN_ATTENTE ? 'PENDING' : 'FAILED'),
            'simulation'   => PaiementService::estReferenceTest($reference),
            'expire_le'    => $etat['tickets']->max('expire_le')?->toIso8601String(),
            // Le contenu des billets (QR) n'est fourni qu'une fois payés
            'tickets'      => $etat['statut'] === PaiementService::PAYE ? $this->reservations->presenterTickets($etat['tickets']) : [],
        ]);
    }

    private function appartientAuClient(Request $request, string $reference): bool
    {
        return Ticket::where('reference_paiement', $reference)
            ->where('utilisateur_id', $request->user()?->id)
            ->exists();
    }

    private function tracerPaiement(string $operateur, PlanificationVoyage $voyage, ?int $ticketId, string $reference,
                                    ?string $externalId, float $montant, string $telephone, string $statut,
                                    ?array $reponse, bool $simulation): void
    {
        if ($operateur === PaiementService::MTN) {
            DB::table('paiements_mtn_momo')->insert([
                'agence_id' => $voyage->agence_id, 'ticket_id' => $ticketId, 'reference_id' => $reference,
                'external_id' => $externalId, 'amount' => $montant, 'currency' => 'XAF', 'payer_msisdn' => $telephone,
                'payer_message' => 'Mobembo Line', 'payee_note' => 'Paiement de billet Mobembo Line',
                'status' => $statut, 'mtn_response' => $reponse ? json_encode($reponse) : null,
                'environment' => config('momo.collection.target_environment') === 'sandbox' ? 'sandbox' : 'production',
                'simulation' => $simulation, 'created_at' => now(), 'updated_at' => now(),
            ]);

            return;
        }

        DB::table('paiements_airtel')->insert([
            'agence_id' => $voyage->agence_id, 'ticket_id' => $ticketId, 'reference_id' => $reference,
            'amount' => $montant, 'currency' => config('airtel.currency', 'XAF'), 'country' => config('airtel.country', 'CG'),
            'payer_msisdn' => $telephone, 'status' => $statut, 'airtel_response' => $reponse ? json_encode($reponse) : null,
            'environment' => config('airtel.environment') === 'production' ? 'production' : 'sandbox',
            'simulation' => $simulation, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }
}
