<?php

namespace App\Support;

use App\Models\CanadianUser;
use App\Models\Utilisateur;
use Illuminate\Support\Facades\DB;

/**
 * Suppression d'un compte client à sa demande (exigence Google Play).
 * Le compte et ses accès sont supprimés ; les billets déjà achetés sont
 * conservés sans lien avec le compte (embarquement et comptabilité).
 */
class SuppressionCompte
{
    public static function client(Utilisateur $user): void
    {
        DB::transaction(function () use ($user) {
            Audit::log('compte.supprime', $user, ['role' => $user->role]);
            $user->tokens()->delete();
            // Suppression définitive (pas de corbeille) : billets et notifications
            // perdent leur lien avec le compte (nullOnDelete)
            $user->forceDelete();
        });
    }

    public static function canadien(CanadianUser $user): void
    {
        DB::transaction(function () use ($user) {
            Audit::log('compte.canadian.supprime', $user);
            $user->tokens()->delete();
            method_exists($user, 'forceDelete') ? $user->forceDelete() : $user->delete();
        });
    }
}
