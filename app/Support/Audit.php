<?php

namespace App\Support;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Journal d'audit : Audit::log('paiement.confirme', $ticket, ['montant' => 7000]).
 * N'interrompt jamais l'opération auditée si l'écriture échoue.
 */
class Audit
{
    public static function log(string $action, ?object $cible = null, array $details = [], ?int $agenceId = null): void
    {
        try {
            [$acteurType, $acteur] = self::acteur();

            DB::table('audit_logs')->insert([
                'acteur_type' => $acteurType,
                'acteur_id'   => $acteur?->getAuthIdentifier(),
                'agence_id'   => $agenceId ?? $cible?->agence_id ?? $acteur?->agence_id ?? null,
                'action'      => $action,
                'cible_type'  => $cible ? class_basename($cible) : null,
                'cible_id'    => $cible?->id ?? null,
                'details'     => $details ? json_encode($details, JSON_UNESCAPED_UNICODE) : null,
                'ip'          => request()?->ip(),
                'user_agent'  => substr((string) request()?->userAgent(), 0, 255) ?: null,
                'created_at'  => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning("Audit impossible ({$action}) : " . $e->getMessage());
        }
    }

    /** @return array{0: string, 1: ?\Illuminate\Contracts\Auth\Authenticatable} */
    private static function acteur(): array
    {
        foreach (['superadmin' => 'superadmin', 'canadian' => 'canadian', 'web' => 'utilisateur', 'sanctum' => 'utilisateur'] as $guard => $type) {
            try {
                if ($u = Auth::guard($guard)->user()) {
                    return [$type, $u];
                }
            } catch (\Throwable) {
                // garde non configurée dans ce contexte
            }
        }

        return ['systeme', null];
    }
}
