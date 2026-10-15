<?php

namespace App\Services;

use Twilio\Rest\Client;
use Twilio\Exceptions\TwilioException;
use Illuminate\Support\Facades\Log;

class TwilioVerifyService
{
    protected ?Client $client = null;
    protected ?string $verifySid = null;

    public function __construct()
    {
        $sid   = config('services.twilio.sid');
        $token = config('services.twilio.auth_token');

        if (empty($sid) || empty($token)) {
            Log::error('Twilio config manquante : TWILIO_SID ou TWILIO_AUTH_TOKEN vide.');
            return;
        }

        $this->client    = new Client($sid, $token);
        $this->verifySid = config('services.twilio.verify_sid');

        if (empty($this->verifySid) || str_starts_with($this->verifySid, 'VAxxxx')) {
            Log::error('TwilioVerifyService : TWILIO_VERIFY_SID manquant ou placeholder.');
        }
    }

    /**
     * Envoie le code par SMS ou, si les SMS n'arrivent pas (ex. réseau Airtel
     * qui filtre les SMS venant de l'étranger), par appel vocal ($canal = 'call').
     */
    public function sendCode(string $phoneNumber, string $canal = 'sms'): array
    {
        if (!$this->client || !$this->verifySid) {
            return [
                'success' => false,
                'message' => 'Configuration Twilio incomplète.',
            ];
        }

        try {
            try {
                $verification = $this->creerVerification($phoneNumber, $this->optionsSms(), $canal);
            } catch (TwilioException $e) {
                // Option refusée par le compte Twilio (ex. 60204 « Custom friendly
                // name not allowed ») : on renvoie le code sans personnalisation
                // plutôt que de bloquer l'inscription.
                if (! in_array($e->getCode(), [60200, 60204, 60205], true)) {
                    throw $e;
                }
                Log::warning('Twilio : personnalisation du SMS refusée, envoi standard', ['code' => $e->getCode(), 'message' => $e->getMessage()]);
                $verification = $this->creerVerification($phoneNumber, [], $canal);
            }

            return [
                'success' => true,
                'status'  => $verification->status, // "pending"
            ];
        } catch (TwilioException $e) {
            Log::error('Twilio sendCode error', [
                'phone'   => $phoneNumber,
                'code'    => $e->getCode(),
                'status'  => method_exists($e, 'getStatusCode') ? $e->getStatusCode() : null,
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        } catch (\Throwable $e) {
            Log::error('Twilio sendCode unexpected error: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    private function creerVerification(string $phoneNumber, array $options, string $canal = 'sms')
    {
        return $this->client->verify->v2
            ->services($this->verifySid)
            ->verifications
            ->create($phoneNumber, $canal, $options);
    }

    /**
     * Personnalisation du SMS envoyé par Twilio Verify :
     *  - en français : « Votre code de vérification <nom du service> est : 123456 »
     *    (le nom est celui du service Verify dans la console Twilio) ;
     *  - nom personnalisé (TWILIO_VERIFY_NOM) seulement si le compte Twilio
     *    l'autorise ;
     *  - modèle de message personnalisé (approuvé par Twilio,
     *    TWILIO_VERIFY_TEMPLATE_SID) s'il est configuré.
     */
    public function optionsSms(): array
    {
        $options = ['locale' => config('services.twilio.verify_locale', 'fr')];

        $nom = config('services.twilio.verify_nom');
        if (! empty($nom)) {
            $options['customFriendlyName'] = mb_substr((string) $nom, 0, 30);
        }

        $modele = config('services.twilio.verify_template_sid');
        if (! empty($modele)) {
            $options['templateSid'] = $modele;
        }

        return $options;
    }

    public function checkCode(string $phoneNumber, string $code): array
    {
        if (!$this->client || !$this->verifySid) {
            return [
                'success' => false,
                'message' => 'Configuration Twilio incomplète.',
            ];
        }

        try {
            $check = $this->client->verify->v2
                ->services($this->verifySid)
                ->verificationChecks
                ->create([
                    'to'   => $phoneNumber,
                    'code' => $code,
                ]);

            return [
                'success' => $check->status === 'approved',
                'status'  => $check->status,
            ];
        } catch (TwilioException $e) {
            Log::error('Twilio checkCode error', [
                'phone'   => $phoneNumber,
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        } catch (\Throwable $e) {
            Log::error('Twilio checkCode unexpected error: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
}