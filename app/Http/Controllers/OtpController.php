<?php

namespace App\Http\Controllers;

use App\Models\Utilisateur;
use App\Services\TwilioVerifyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class OtpController extends Controller
{
    protected TwilioVerifyService $twilioVerify;

    public function __construct(TwilioVerifyService $twilioVerify)
    {
        $this->twilioVerify = $twilioVerify;
    }

    /**
     * Convertit un numéro local congolais (ex: 061234567)
     * vers le format E.164 attendu par Twilio (+242061234567)
     */
    private function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/\s+/', '', $phone);
        return '+242' . $phone;
    }

    /**
     * Envoie un code OTP par SMS via Twilio Verify.
     * ⭐ type_usage est désormais OPTIONNEL (défaut = INSCRIPTION).
     */
    public function send(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'telephone'  => 'required|string|size:9',
            'type_usage' => 'nullable|in:INSCRIPTION,RESET_PASSWORD',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors()
            ], 422);
        }

        $telephoneE164 = $this->normalizePhone($request->telephone);
        $typeUsage     = $request->input('type_usage', 'INSCRIPTION');

        $result = $this->twilioVerify->sendCode($telephoneE164);

        if (!$result['success']) {
            // ⭐ Si Twilio échoue → on bascule en mode DEV automatiquement
            Log::warning('Twilio KO → bascule en mode dev pour ' . $request->telephone);

            $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            Cache::put('otp_' . $request->telephone, $code, now()->addMinutes(10));

            Log::info("OTP DEV (fallback) pour {$request->telephone} : $code");

            return response()->json([
                'message'    => 'Code OTP généré (mode dev — Twilio indisponible).',
                'status'     => 'dev',
                'dev_mode'   => true,
                'code'       => $code,   // ⚠️ Retirer en production
                'telephone'  => $request->telephone,
            ], 200);
        }

        return response()->json([
            'message' => 'Code OTP envoyé avec succès par SMS',
            'status'  => $result['status'],
        ], 200);
    }

    /**
     * ⭐ Fallback DEV explicite — sans Twilio
     * POST /api/send-otp-dev
     */
    public function sendDev(Request $request)
    {
        $request->validate([
            'telephone' => 'required|string|size:9',
        ]);

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        Cache::put('otp_' . $request->telephone, $code, now()->addMinutes(10));

        Log::info("OTP DEV pour {$request->telephone} : $code");

        return response()->json([
            'success'   => true,
            'message'   => 'Code OTP généré (mode dev).',
            'code'      => $code,
            'telephone' => $request->telephone,
            'dev_mode'  => true,
        ], 200);
    }

    /**
     * Vérifie la validité du code OTP.
     * ⭐ type_usage est désormais OPTIONNEL.
     */
    public function verify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'telephone'  => 'required|string|size:9',
            'code'       => 'required|string|size:6',
            'type_usage' => 'nullable|in:INSCRIPTION,RESET_PASSWORD',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors()
            ], 422);
        }

        $telephoneE164 = $this->normalizePhone($request->telephone);
        $typeUsage     = $request->input('type_usage', 'INSCRIPTION');

        // ⭐ On essaie d'abord le cache local (fallback dev)
        $cacheKey    = 'otp_' . $request->telephone;
        $codeAttendu = Cache::get($cacheKey);

        $isValid = false;

        if ($codeAttendu && $codeAttendu === $request->code) {
            $isValid = true;
            Cache::forget($cacheKey);
        } else {
            // Sinon, on tente la vérification Twilio
            $result = $this->twilioVerify->checkCode($telephoneE164, $request->code);
            $isValid = $result['success'];
        }

        if (!$isValid) {
            return response()->json([
                'message' => 'Code incorrect ou expiré.',
            ], 400);
        }

        // Marque le compte comme confirmé si inscription
        if ($typeUsage === 'INSCRIPTION') {
            Utilisateur::where('telephone', $request->telephone)
                ->update(['compte_confirme' => true]);
        }

        return response()->json([
            'message' => 'Code validé avec succès.',
        ], 200);
    }
}