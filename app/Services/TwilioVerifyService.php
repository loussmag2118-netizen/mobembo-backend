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

    public function sendCode(string $phoneNumber): array
    {
        if (!$this->client || !$this->verifySid) {
            return [
                'success' => false,
                'message' => 'Configuration Twilio incomplète.',
            ];
        }

        try {
            $verification = $this->client->verify->v2
                ->services($this->verifySid)
                ->verifications
                ->create($phoneNumber, 'sms', $this->optionsSms());

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

    /**
     * Personnalisation du SMS envoyé par Twilio Verify :
     *  - en français, au nom de « Mobembo Line » :
     *    « Votre code de vérification Mobembo Line est : 123456 » ;
     *  - avec un modèle de message personnalisé (approuvé par Twilio,
     *    TWILIO_VERIFY_TEMPLATE_SID) s'il est configuré.
     */
    public function optionsSms(): array
    {
        $options = [
            'locale'             => config('services.twilio.verify_locale', 'fr'),
            'customFriendlyName' => mb_substr((string) config('services.twilio.verify_nom', 'Mobembo Line'), 0, 30),
        ];

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