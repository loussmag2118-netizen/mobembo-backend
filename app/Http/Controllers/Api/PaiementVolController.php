<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\ReservationImpossibleException;
use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Services\PaiementService;
use App\Services\ReservationVolService;
use App\Support\Audit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Paiement d'une réservation de vol par MTN MoMo ou Airtel Money.
 *
 *  1. POST /api/canadian/paiements/initier : demande envoyée au téléphone ;
 *  2. GET  /api/canadian/paiements/{reference}/statut : consulté par
 *     l'application jusqu'à la validation (le serveur interroge l'opérateur).
 *
 * La réservation n'est payée qu'après confirmation de l'opérateur.
 */
class PaiementVolController extends Controller
{
    private const OPERATEURS = [
        'mtn_momo'     => PaiementService::MTN,
        'mtn'          => PaiementService::MTN,
        'mobile_money' => PaiementService::MTN,
        'airtel_money' => PaiementService::AIRTEL,
        'airtel'       => PaiementService::AIRTEL,
    ];

    public function __construct(
        private PaiementService $paiements,
        private ReservationVolService $reservations,
    ) {
    }

    public function initier(Request $request): JsonResponse
    {
        $operateur = self::OPERATEURS[strtolower((string) $request->input('methode', $request->input('methode_paiement', 'mtn_momo')))] ?? null;
        if (! $operateur) {
            return response()->json(['success' => false, 'message' => 'Moyen de paiement non disponible : choisissez MTN Mobile Money ou Airtel Money.'], 422);
        }

        $this->reservations->expirer();
        $reservation = Reservation::with('vol')
            ->where('id', $request->input('reservation_id', $request->input('reservationId')))
            ->where('canadian_user_id', $request->user()->id)
            ->first();
        if (! $reservation) {
            return response()->json(['success' => false, 'message' => 'Réservation introuvable.'], 404);
        }
        if ($reservation->statut !== 'En_Attente') {
            return $this->reponse($reservation, $reservation->statut === 'Expiree'
                ? 'Le délai de paiement est dépassé : refaites la réservation.'
                : null, $reservation->statut === 'Expiree' ? 410 : 409);
        }

        // Paiement déjà lancé et toujours en cours : on ne redemande pas
        if ($reservation->reference_paiement && $this->statutOperateur($reservation) === 'PENDING') {
            return $this->reponse($reservation, 'Une demande de paiement est déjà en cours : validez-la sur votre téléphone.');
        }

        $telephone = preg_replace('/\D/', '', (string) $request->input('telephone', $request->user()->telephone));
        $telephone = preg_replace('/^242/', '', $telephone);
        $prefixe = $operateur === PaiementService::MTN ? '06' : '05';
        if (! preg_match('/^' . $prefixe . '\d{7}$/', $telephone)) {
            return response()->json([
                'success' => false,
                'message' => "Pour payer avec {$operateur}, saisissez un numéro {$operateur} qui commence par {$prefixe} (9 chiffres).",
            ], 422);
        }

        $montant = (float) $reservation->montant_total;

        try {
            // ---------------- MODE TEST (jamais en production sans PAIEMENT_MODE_TEST)
            if ($this->paiements->modeTest($operateur)) {
                $reference = ($operateur === PaiementService::MTN ? 'SIM-' : 'AIRTEL-SIM-') . strtoupper(Str::random(10));
                $reservation->update(['reference_paiement' => $reference, 'methode_paiement' => $operateur]);
                $this->tracer($operateur, $reservation, $reference, null, $montant, $telephone,
                    $operateur === PaiementService::MTN ? 'SUCCESSFUL' : 'SUCCESS', null, true);
                $this->reservations->confirmer($reservation, $operateur, $reference);
                Audit::log('paiement.test', $reservation, ['reference' => $reference, 'montant' => $montant], $reservation->agence_id);

                return $this->reponse($reservation->fresh('vol'), 'Paiement de TEST validé (aucun débit réel).');
            }
        } catch (ReservationImpossibleException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], $e->statutHttp());
        }

        // ---------------- MODE RÉEL
        $jeton = $operateur === PaiementService::MTN ? $this->paiements->jetonMtn() : $this->paiements->jetonAirtel();
        if (! $jeton) {
            return response()->json(['success' => false, 'message' => "Le service {$operateur} ne répond pas. Réessayez dans quelques instants."], 502);
        }

        $reference  = $operateur === PaiementService::MTN ? (string) Str::uuid() : 'AIRTEL-' . strtoupper((string) Str::uuid());
        $externalId = PaiementService::externalIdMtn();

        try {
            $reponse = $operateur === PaiementService::MTN
                ? $this->paiements->demanderMtn($jeton, $reference, $externalId, $montant, $telephone, 'billet d\'avion')
                : $this->paiements->demanderAirtel($jeton, $reference, $montant, $telephone);
        } catch (\Throwable $e) {
            Log::error("Demande de paiement {$operateur} (vol) : " . $e->getMessage());

            return response()->json(['success' => false, 'message' => "Le service {$operateur} ne répond pas. Réessayez."], 502);
        }

        $accepte = $operateur === PaiementService::MTN
            ? $reponse->status() === 202
            : in_array($reponse->status(), [200, 201, 202]) && data_get($reponse->json(), 'status.success', true) !== false;

        $this->tracer($operateur, $reservation, $reference, $externalId, $montant, $telephone,
            $accepte ? 'PENDING' : 'FAILED', $reponse->json(), false);

        if (! $accepte) {
            Audit::log('paiement.refuse_initiation', $reservation, ['reference' => $reference, 'http' => $reponse->status()], $reservation->agence_id);

            return response()->json([
                'success' => false,
                'message' => "Le paiement {$operateur} n'a pas pu être lancé. Vérifiez le numéro et réessayez.",
            ], $reponse->status() >= 400 ? $reponse->status() : 502);
        }

        // Le délai est prolongé le temps de valider sur le téléphone
        $reservation->update([
            'reference_paiement' => $reference,
            'methode_paiement'   => $operateur,
            'expire_le'          => now()->addMinutes(ReservationVolService::DELAI_MINUTES),
        ]);
        Audit::log('paiement.initie', $reservation, ['reference' => $reference, 'operateur' => $operateur, 'montant' => $montant], $reservation->agence_id);

        return $this->reponse($reservation->fresh('vol'), "Demande {$operateur} envoyée. Validez le paiement sur votre téléphone.");
    }

    /** GET /api/canadian/paiements/{reference}/statut */
    public function statut(Request $request, string $reference): JsonResponse
    {
        $reservation = Reservation::with('vol')
            ->where('reference_paiement', $reference)
            ->where('canadian_user_id', $request->user()->id)
            ->first();
        if (! $reservation) {
            return response()->json(['success' => false, 'message' => 'Paiement introuvable.'], 404);
        }

        $this->actualiser($reservation);

        return $this->reponse($reservation->fresh('vol'));
    }

    /**
     * Interroge l'opérateur et applique le résultat. Sans effet sur une
     * réservation déjà réglée ou sur un paiement de test.
     */
    public function actualiser(Reservation $reservation): void
    {
        $this->reservations->expirer();
        $reservation->refresh();

        $reference = $reservation->reference_paiement;
        if (! $reference || PaiementService::estReferenceTest($reference)
            || ! in_array($reservation->statut, ['En_Attente', 'Expiree'], true)) {
            return;
        }

        try {
            $resultat = $reservation->methode_paiement === PaiementService::AIRTEL
                ? $this->paiements->statutAirtel($reference)
                : $this->paiements->statutMtn($reference);

            if ($resultat['statut'] === PaiementService::PAYE) {
                $this->reservations->confirmer($reservation, $reservation->methode_paiement ?? PaiementService::MTN, $resultat['transaction']);
            } elseif ($resultat['statut'] === PaiementService::ECHOUE && $reservation->statut === 'En_Attente') {
                $this->reservations->liberer($reservation, 'Echouee');
                Audit::log('paiement.echoue', $reservation, ['reference' => $reference], $reservation->agence_id);
            }
        } catch (\Throwable $e) {
            Log::warning("Statut du paiement {$reference} (vol) indisponible : " . $e->getMessage());
        }
    }

    // ------------------------------------------------------------------

    private function statutOperateur(Reservation $reservation): ?string
    {
        $table = $reservation->methode_paiement === PaiementService::AIRTEL ? 'paiements_airtel' : 'paiements_mtn_momo';

        return DB::table($table)->where('reference_id', $reservation->reference_paiement)->value('status');
    }

    private function reponse(Reservation $reservation, ?string $message = null, int $code = 200): JsonResponse
    {
        $statut = match ($reservation->statut) {
            'Paye', 'Confirmee' => PaiementService::PAYE,
            'En_Attente'        => PaiementService::EN_ATTENTE,
            'A_Rembourser'      => PaiementService::A_REMBOURSER,
            default             => PaiementService::ECHOUE,
        };
        $messages = [
            PaiementService::PAYE         => 'Paiement confirmé. Votre billet est prêt.',
            PaiementService::EN_ATTENTE   => 'En attente de votre validation sur le téléphone.',
            PaiementService::ECHOUE       => 'Le paiement n\'a pas abouti. Aucun billet n\'a été émis.',
            PaiementService::A_REMBOURSER => 'Paiement reçu après expiration de la réservation : un remboursement va être effectué.',
        ];

        return response()->json([
            // « success » = billet payé (les anciennes versions de l'application s'y fient)
            'success'        => $code < 400 && $statut === PaiementService::PAYE,
            'demande_envoyee'=> $code < 400 && $statut === PaiementService::EN_ATTENTE,
            'message'        => $message ?? $messages[$statut],
            'statut'         => $statut,
            'reference'      => $reservation->reference_paiement,
            'reservation_id' => $reservation->id,
            'montant'        => (float) $reservation->montant_total,
            'methode'        => $reservation->methode_paiement,
            'simulation'     => PaiementService::estReferenceTest($reservation->reference_paiement),
            'expire_le'      => $statut === PaiementService::EN_ATTENTE ? $reservation->expire_le?->toIso8601String() : null,
            'code_pnr'       => $statut === PaiementService::PAYE ? $reservation->code_pnr : null,
        ], $code);
    }

    private function tracer(string $operateur, Reservation $reservation, string $reference, ?string $externalId,
                            float $montant, string $telephone, string $statut, ?array $reponse, bool $simulation): void
    {
        $commun = [
            'agence_id' => $reservation->agence_id, 'ticket_id' => null, 'reservation_id' => $reservation->id,
            'reference_id' => $reference, 'amount' => $montant, 'payer_msisdn' => $telephone, 'status' => $statut,
            'simulation' => $simulation, 'created_at' => now(), 'updated_at' => now(),
        ];

        if ($operateur === PaiementService::MTN) {
            DB::table('paiements_mtn_momo')->insert($commun + [
                'external_id' => $externalId, 'currency' => 'XAF',
                'payer_message' => 'Mobembo Line', 'payee_note' => 'Billet d\'avion Mobembo Line',
                'mtn_response' => $reponse ? json_encode($reponse) : null,
                'environment' => config('momo.collection.target_environment') === 'sandbox' ? 'sandbox' : 'production',
            ]);

            return;
        }

        DB::table('paiements_airtel')->insert($commun + [
            'currency' => config('airtel.currency', 'XAF'), 'country' => config('airtel.country', 'CG'),
            'airtel_response' => $reponse ? json_encode($reponse) : null,
            'environment' => config('airtel.environment') === 'production' ? 'production' : 'sandbox',
        ]);
    }
}
