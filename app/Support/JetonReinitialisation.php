<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Preuve qu'un numéro a reçu le code de réinitialisation : délivrée par la
 * vérification du code (OTP), exigée pour changer le mot de passe, valable
 * 15 minutes et une seule fois.
 */
class JetonReinitialisation
{
    private static function cle(string $telephone): string
    {
        return 'reinit_mdp_' . $telephone;
    }

    public static function emettre(string $telephone): string
    {
        $jeton = Str::random(48);
        Cache::put(self::cle($telephone), hash('sha256', $jeton), now()->addMinutes(15));

        return $jeton;
    }

    /** Vérifie le jeton et le supprime (usage unique). */
    public static function consommer(string $telephone, ?string $jeton): bool
    {
        $attendu = Cache::get(self::cle($telephone));
        if (! $attendu || ! $jeton || ! hash_equals($attendu, hash('sha256', $jeton))) {
            return false;
        }
        Cache::forget(self::cle($telephone));

        return true;
    }
}
