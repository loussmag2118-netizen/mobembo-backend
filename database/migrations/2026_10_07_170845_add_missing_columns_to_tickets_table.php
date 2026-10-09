<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            // 1. Colonne agence_id
            if (!Schema::hasColumn('tickets', 'agence_id')) {
                $table->unsignedBigInteger('agence_id')->nullable()->after('id');
                $table->index('agence_id');
            }

            // 2. Colonne agent_vendeur_id (pour tracer qui a vendu le ticket)
            if (!Schema::hasColumn('tickets', 'agent_vendeur_id')) {
                $table->unsignedBigInteger('agent_vendeur_id')->nullable()->after('agence_id');
                $table->index('agent_vendeur_id');
            }

            // 3. Colonnes de gares si utilisées
            if (!Schema::hasColumn('tickets', 'gare_depart_id')) {
                $table->unsignedBigInteger('gare_depart_id')->nullable();
            }
            if (!Schema::hasColumn('tickets', 'gare_arrivee_id')) {
                $table->unsignedBigInteger('gare_arrivee_id')->nullable();
            }
        });

        // Remplir agence_id à partir de la planification liée
        if (Schema::hasColumn('tickets', 'planification_voyage_id')) {
            DB::statement('
                UPDATE tickets t
                INNER JOIN planification_voyages pv ON t.planification_voyage_id = pv.id
                SET t.agence_id = pv.agence_id
                WHERE t.agence_id IS NULL
            ');
        }
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            if (Schema::hasColumn('tickets', 'agence_id')) {
                $table->dropIndex(['agence_id']);
                $table->dropColumn('agence_id');
            }
            if (Schema::hasColumn('tickets', 'agent_vendeur_id')) {
                $table->dropIndex(['agent_vendeur_id']);
                $table->dropColumn('agent_vendeur_id');
            }
            if (Schema::hasColumn('tickets', 'gare_depart_id')) {
                $table->dropColumn('gare_depart_id');
            }
            if (Schema::hasColumn('tickets', 'gare_arrivee_id')) {
                $table->dropColumn('gare_arrivee_id');
            }
        });
    }
};