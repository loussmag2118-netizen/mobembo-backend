<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ⭐ CORRECTION DES CONTRAINTES DE LA TABLE UTILISATEURS
     *
     * Problèmes résolus :
     * 1. `agence_id` était NOT NULL → les clients sans agence ne peuvent pas s'inscrire.
     * 2. `role` ENUM n'incluait pas 'Client' → insertion impossible pour les clients.
     */
    public function up(): void
    {
        // ⚠️ MySQL nécessite de modifier l'ENUM via ALTER TABLE brut car Blueprint
        // ne supporte pas directement le changement d'un ENUM existant.

        // 1️⃣  Rendre agence_id nullable (supprime la contrainte FK, modifie la colonne, remet la FK)
        Schema::table('utilisateurs', function (Blueprint $table) {
            // Supprime la clé étrangère existante
            $table->dropForeign(['agence_id']);
        });

        Schema::table('utilisateurs', function (Blueprint $table) {
            // Rend la colonne nullable
            $table->unsignedBigInteger('agence_id')->nullable()->change();
        });

        Schema::table('utilisateurs', function (Blueprint $table) {
            // Remet la contrainte FK (nullable compatible)
            $table->foreign('agence_id')
                  ->references('id')
                  ->on('agences')
                  ->onDelete('set null');
        });

        // 2️⃣  Élargir l'ENUM role pour inclure 'Client' et 'SuperAdmin'
        DB::statement("ALTER TABLE utilisateurs MODIFY COLUMN role ENUM('Admin', 'Agent', 'Client', 'SuperAdmin') NOT NULL DEFAULT 'Client'");

        // 3️⃣  Rendre gare_id nullable s'il ne l'est pas encore (colonne ajoutée plus tard)
        if (Schema::hasColumn('utilisateurs', 'gare_id')) {
            Schema::table('utilisateurs', function (Blueprint $table) {
                $table->unsignedBigInteger('gare_id')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        // Revenir à l'état initial (attention : perte des lignes avec role='Client')
        DB::statement("ALTER TABLE utilisateurs MODIFY COLUMN role ENUM('Admin', 'Agent') NOT NULL");

        Schema::table('utilisateurs', function (Blueprint $table) {
            $table->dropForeign(['agence_id']);
        });

        Schema::table('utilisateurs', function (Blueprint $table) {
            $table->unsignedBigInteger('agence_id')->nullable(false)->change();
            $table->foreign('agence_id')->references('id')->on('agences')->onDelete('cascade');
        });
    }
};
