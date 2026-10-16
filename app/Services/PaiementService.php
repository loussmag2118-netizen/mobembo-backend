<?php

namespace App\Services;

use App\Exceptions\ReservationImpossibleException;
use App\Models\PlanificationVoyage;
use App\Models\Ticket;
use App\Support\Audit;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Paiements MTN MoMo et Airtel Money.
 *
 * Règles :
 *  - un billet n'est « Paye » qu'après confirmation de l'opérateur, obtenue
 *    côté serveur par la consultation de son API (jamais sur la seule foi
 *    d'un message reçu ou d'une déclaration du client) ;
 *  - confirmer() est idempotent : appelé deux fois, il ne fait rien de plus ;
 *  - aucun paiement n'est simulé en production, sauf mode test explicite
 *    (PAIEMENT_MODE_TEST) ; les billets de test sont alors identifiables.
 */
class PaiementService
{
    public const MTN    = 'MTN';
    public const AIRTEL = 'Airtel';

    // Statuts normalisés renvoyés à l'application
    public const EN_ATTENTE   = 'EN_ATTENTE';
    public const PAYE         = 'PAYE';
    public const ECHOUE       = 'ECHOUE';
    public const A_REMBOURSER = 'A_REMBOURSER';

    public function __construct(private ReservationService $reservations)
    {
    }

    // ==================================================================
    // MODE TEST
    // ==================================================================

    /**
     * Indique si le paiement doit être simulé pour cet opérateur.
     * Lève une erreur 503 si le paiement réel n'est pas encore configuré
     * et que la simulation n'est pas autorisée (production).
     */
    public function modeTest(string $operateur): bool
    {
        $clesPresentes = $operateur === self::MTN
            ? filled(config('momo.collection.api_user')) && filled(config('momo.collection.api_key')) && filled(config('momo.collection.primary_key'))
            : filled(config('airtel.client_id')) && filled(config('airtel.client_secret'));

        $simulationDemandee = filter_var(
            $operateur === self::MTN ? config('momo.simulation') : config('airtel.simulation'),
            FILTER_VALIDATE_BOOLEAN
        );

        if ($this->simulationAutorisee() && (! $clesPresentes || $simulationDemandee)) {
            return true;
        }

        if (! $clesPresentes) {
            throw new ReservationImpossibleException(
                "Le paiement {$operateur} n'est pas encore disponible. Réessayez plus tard.", 503
            );
        }

        return false;
    }

    public function simulationAutorisee(): bool
    {
        return ! app()->environment('production') || (bool) config('paiement.mode_test_production');
    }

    public static function estReferenceTest(?string $reference): bool
    {
        return $reference !== null && (str_starts_with($reference, 'SIM-') || str_starts_with($reference, 'AIRTEL-SIM-'));
    }

    // ==================================================================
    // MTN MOMO
    // ==================================================================

    private function urlsMtn(): array
    {
        $env = config('momo.collection.target_environment', 'sandbox');

        return config('momo.urls.' . ($env === 'sandbox' ? 'sandbox' : 'production'));
    }

    public function jetonMtn(): ?string
    {
        try {
            $r = Http::timeout(20)->withHeaders([
                'Ocp-Apim-Subscription-Key' => config('momo.collection.primary_key'),
                'Authorization' => 'Basic ' . base64_encode(config('momo.collection.api_user') . ':' . config('momo.collection.api_key')),
            ])->post($this->urlsMtn()['token']);

            if ($r->successful()) {
                return $r->json('access_token');
            }
            Log::error('Jeton MTN refusé : ' . $r->body());
        } catch (\Throwable $e) {
            Log::error('Jeton MTN : ' . $e->getMessage());
        }

        return null;
    }

    /** Numéro au format attendu par MTN : « 242 » + 9 chiffres (ex. 242061234567). */
    public static function msisdnCongo(string $telephone): string
    {
        $chiffres = preg_replace('/\D/', '', $telephone);

        return str_starts_with($chiffres, '242') ? $chiffres : '242' . $chiffres;
    }

    /**
     * Identifiant de la transaction chez MTN, propre à la plateforme : le
     * compte MTN est partagé entre plusieurs plateformes, le préfixe
     * « MOBEMBO- » permet de retrouver et rapprocher les paiements Mobembo.
     */
    public static function externalIdMtn(): string
    {
        return 'MOBEMBO-' . now()->format('YmdHis') . '-' . random_int(1000, 9999);
    }

    public function demanderMtn(string $jeton, string $reference, string $externalId, float $montant, string $telephone, string $objet = 'billet de bus'): Response
    {
        $entetes = [
            'Authorization'             => 'Bearer ' . $jeton,
            'X-Reference-Id'            => $reference,
            'X-Target-Environment'      => config('momo.collection.target_environment', 'sandbox'),
            'Ocp-Apim-Subscription-Key' => config('momo.collection.primary_key'),
            'Content-Type'              => 'application/json',
        ];
        if ($callback = config('momo.callback_url')) {
            $entetes['X-Callback-Url'] = $callback;
        }

        return Http::timeout(30)->withHeaders($entetes)->post($this->urlsMtn()['pay'], [
            'amount'       => (string) (int) round($montant),
            'currency'     => 'XAF',
            'externalId'   => $externalId,
            'payer'        => ['partyIdType' => 'MSISDN', 'partyId' => self::msisdnCongo($telephone)],
            // Nom de la plateforme sur chaque transaction (suivi et rapprochement)
            'payerMessage' => 'Mobembo Line',
            'payeeNote'    => 'Mobembo Line - ' . $objet . ' ' . $externalId,
        ]);
    }

    /** @return array{statut: string, brut: ?array, transaction: ?string} */
    public function statutMtn(string $reference): array
    {
        $jeton = $this->jetonMtn();
        if (! $jeton) {
            return ['statut' => self::EN_ATTENTE, 'brut' => null, 'transaction' => null];
        }

        $r = Http::timeout(20)->withHeaders([
            'Authorization'             => 'Bearer ' . $jeton,
            'X-Target-Environment'      => config('momo.collection.target_environment', 'sandbox'),
            'Ocp-Apim-Subscription-Key' => config('momo.collection.primary_key'),
        ])->get(rtrim($this->urlsMtn()['pay'], '/') . '/' . $reference);

        if (! $r->successful()) {
            return ['statut' => self::EN_ATTENTE, 'brut' => $r->json(), 'transaction' => null];
        }

        $brut = $r->json() ?? [];
        $statut = match (strtoupper((string) ($brut['status'] ?? ''))) {
            'SUCCESSFUL'                    => self::PAYE,
            'FAILED', 'REJECTED', 'TIMEOUT' => self::ECHOUE,
            default                         => self::EN_ATTENTE,
        };

        DB::table('paiements_mtn_momo')->where('reference_id', $reference)->update([
            'status'                   => in_array($brut['status'] ?? '', ['SUCCESSFUL', 'FAILED', 'REJECTED', 'TIMEOUT']) ? $brut['status'] : 'PENDING',
            'financial_transaction_id' => $brut['financialTransactionId'] ?? null,
            'mtn_response'             => json_encode($brut),
            'updated_at'               => now(),
        ]);

        return ['statut' => $statut, 'brut' => $brut, 'transaction' => $brut['financialTransactionId'] ?? null];
    }

    // ==================================================================
    // AIRTEL MONEY
    // ==================================================================

    private function urlsAirtel(): array
    {
        return config('airtel.urls.' . (config('airtel.environment') === 'production' ? 'production' : 'sandbox'));
    }

    private function entetesAirtel(string $jeton): array
    {
        return [
            'Authorization' => 'Bearer ' . $jeton,
            'Content-Type'  => 'application/json',
            'X-Country'     => config('airtel.country', 'CG'),
            'X-Currency'    => config('airtel.currency', 'XAF'),
        ];
    }

    public function jetonAirtel(): ?string
    {
        try {
            $r = Http::timeout(20)->post($this->urlsAirtel()['token'], [
                'client_id'     => config('airtel.client_id'),
                'client_secret' => config('airtel.client_secret'),
                'grant_type'    => 'client_credentials',
            ]);
            if ($r->successful()) {
                return $r->json('access_token');
            }
            Log::error('Jeton Airtel refusé : ' . $r->body());
        } catch (\Throwable $e) {
            Log::error('Jeton Airtel : ' . $e->getMessage());
        }

        return null;
    }

    public function demanderAirtel(string $jeton, string $reference, float $montant, string $telephone): Response
    {
        // Airtel attend le numéro sans indicatif pays
        $msisdn = preg_replace('/^(\+?242)/', '', $telephone);

        return Http::timeout(30)->withHeaders($this->entetesAirtel($jeton))->post($this->urlsAirtel()['pay'], [
            'reference'   => 'Mobembo Line',
            'subscriber'  => ['country' => config('airtel.country', 'CG'), 'currency' => config('airtel.currency', 'XAF'), 'msisdn' => $msisdn],
            'transaction' => ['amount' => (int) round($montant), 'country' => config('airtel.country', 'CG'), 'currency' => config('airtel.currency', 'XAF'), 'id' => $reference],
        ]);
    }

    /** @return array{statut: string, brut: ?array, transaction: ?string} */
    public function statutAirtel(string $reference): array
    {
        $jeton = $this->jetonAirtel();
        if (! $jeton) {
            return ['statut' => self::EN_ATTENTE, 'brut' => null, 'transaction' => null];
        }

        $r = Http::timeout(20)->withHeaders($this->entetesAirtel($jeton))->get($this->urlsAirtel()['status'] . $reference);
        if (! $r->successful()) {
            return ['statut' => self::EN_ATTENTE, 'brut' => $r->json(), 'transaction' => null];
        }

        $brut = $r->json() ?? [];
        // TS = réussi, TF = échoué, TE = expiré, TIP / TA = en cours
        $code = strtoupper((string) data_get($brut, 'data.transaction.status', ''));
        $statut = match ($code) {
            'TS'       => self::PAYE,
            'TF', 'TE' => self::ECHOUE,
            default    => self::EN_ATTENTE,
        };

        DB::table('paiements_airtel')->where('reference_id', $reference)->update([
            'status'          => match ($statut) { self::PAYE => 'SUCCESS', self::ECHOUE => 'FAILED', default => 'PENDING' },
            'airtel_money_id' => data_get($brut, 'data.transaction.airtel_money_id'),
            'airtel_response' => json_encode($brut),
            'updated_at'      => now(),
        ]);

        return ['statut' => $statut, 'brut' => $brut, 'transaction' => data_get($brut, 'data.transaction.airtel_money_id')];
    }

    // ==================================================================
    // CONFIRMATION / ACTUALISATION
    // ==================================================================

    /**
     * Interroge l'opérateur et applique le résultat (paiement confirmé,
     * échoué ou toujours en attente). Sans effet sur un paiement déjà réglé.
     *
     * @return array{statut: string, tickets: Collection<int, Ticket>}|null  null si la référence est inconnue
     */
    public function actualiser(string $reference): ?array
    {
        $this->reservations->expirerReservations();

        $tickets = Ticket::where('reference_paiement', $reference)->get();
        if ($tickets->isEmpty()) {
            return null;
        }

        $dejaRegle = $tickets->every(fn ($t) => $t->statut_paiement === 'Paye');

        if (! $dejaRegle && ! self::estReferenceTest($reference) && ! $this->resultatDefinitifConnu($tickets->first()->mode_paiement, $reference)) {
            try {
                $resultat = $tickets->first()->mode_paiement === self::MTN
                    ? $this->statutMtn($reference)
                    : $this->statutAirtel($reference);

                if ($resultat['statut'] === self::PAYE) {
                    $this->confirmer($reference, $resultat['transaction']);
                } elseif ($resultat['statut'] === self::ECHOUE) {
                    $this->reservations->liberer($reference);
                    Audit::log('paiement.echoue', $tickets->first(), ['reference' => $reference]);
                }
            } catch (\Throwable $e) {
                Log::warning("Statut du paiement {$reference} indisponible : " . $e->getMessage());
            }
        }

        $tickets = Ticket::where('reference_paiement', $reference)->get();

        return ['statut' => $this->statutGlobal($tickets, $reference), 'tickets' => $tickets];
    }

    /**
     * Marque les billets comme payés après confirmation de l'opérateur.
     * Idempotent. Si la réservation avait expiré entre-temps, les billets
     * sont réactivés s'il reste de la place ; sinon le paiement est signalé
     * « à rembourser » dans le journal d'audit.
     */
    public function confirmer(string $reference, ?string $transactionOperateur = null): void
    {
        DB::transaction(function () use ($reference, $transactionOperateur) {
            $voyageIds = Ticket::where('reference_paiement', $reference)->pluck('planification_voyage_id')->unique()->filter();

            foreach ($voyageIds as $voyageId) {
                $voyage = PlanificationVoyage::whereKey($voyageId)->lockForUpdate()->first();
                if (! $voyage) {
                    continue;
                }

                $tickets = Ticket::where('reference_paiement', $reference)
                    ->where('planification_voyage_id', $voyageId)
                    ->whereIn('statut_paiement', ['En_Attente', 'Echoue'])
                    ->get();
                if ($tickets->isEmpty()) {
                    continue;   // déjà confirmé
                }

                $this->reservations->recalculerPlaces($voyage);
                $expires = $tickets->where('statut_paiement', 'Echoue');

                // Siège revendu entre-temps : le billet expiré ne peut pas être réactivé
                $siegesPris = array_flip($this->reservations->siegesOccupes($voyage));
                $siegeRevendu = $expires->contains(fn ($t) => $t->numero_siege !== null && isset($siegesPris[$t->numero_siege]));

                if ($expires->isNotEmpty() && ($siegeRevendu || $voyage->fresh()->places_disponibles < $expires->count())) {
                    // Le client a payé mais sa place a été revendue entre-temps
                    Log::critical("Paiement {$reference} confirmé après expiration, plus de place : remboursement nécessaire.");
                    Audit::log('paiement.a_rembourser', $tickets->first(), [
                        'reference' => $reference, 'transaction' => $transactionOperateur, 'billets' => $expires->count(),
                    ]);
                    $tickets = $tickets->where('statut_paiement', 'En_Attente');
                }

                Ticket::whereIn('id', $tickets->pluck('id'))->update([
                    'statut_paiement' => 'Paye',
                    'date_paiement'   => now(),
                    'expire_le'       => null,
                ]);
                $this->reservations->recalculerPlaces($voyage->fresh());

                if ($tickets->isNotEmpty()) {
                    Audit::log('paiement.confirme', $tickets->first(), [
                        'reference' => $reference, 'transaction' => $transactionOperateur, 'billets' => $tickets->count(),
                    ]);
                }
            }
        });
    }

    /**
     * Statut à afficher au client pour un paiement.
     *
     * @param  Collection<int, Ticket>  $tickets
     */
    public function statutGlobal(Collection $tickets, string $reference): string
    {
        if ($tickets->isNotEmpty() && $tickets->every(fn ($t) => $t->statut_paiement === 'Paye')) {
            return self::PAYE;
        }
        if ($tickets->contains(fn ($t) => $t->statut_paiement === 'En_Attente')) {
            return self::EN_ATTENTE;
        }
        if ($this->paiementReussiChezOperateur($tickets->first()?->mode_paiement, $reference)) {
            return self::A_REMBOURSER;
        }

        return self::ECHOUE;
    }

    private function resultatDefinitifConnu(?string $operateur, string $reference): bool
    {
        $statut = $this->statutEnregistre($operateur, $reference);

        return in_array($statut, ['SUCCESSFUL', 'FAILED', 'REJECTED', 'TIMEOUT', 'SUCCESS', 'CANCELLED'], true);
    }

    private function paiementReussiChezOperateur(?string $operateur, string $reference): bool
    {
        return in_array($this->statutEnregistre($operateur, $reference), ['SUCCESSFUL', 'SUCCESS'], true);
    }

    private function statutEnregistre(?string $operateur, string $reference): ?string
    {
        $table = $operateur === self::MTN ? 'paiements_mtn_momo' : 'paiements_airtel';

        return DB::table($table)->where('reference_id', $reference)->value('status');
    }
}
