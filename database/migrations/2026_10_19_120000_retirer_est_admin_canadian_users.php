<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Les administrateurs aériens sont désormais des comptes « utilisateurs »
 * rattachés à leur compagnie : la colonne est_admin des clients ne sert plus.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('canadian_users', 'est_admin')) {
            Schema::table('canadian_users', function (Blueprint $table) {
                $table->dropIndex(['est_admin']);
                $table->dropColumn('est_admin');
            });
        }
    }

    public function down(): void
    {
        // Colonne abandonnée : rien à recréer
    }
};
