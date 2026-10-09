<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vols', function (Blueprint $table) {
            // Ajoute la colonne 'avion' APRÈS 'compagnie'
            if (!Schema::hasColumn('vols', 'avion')) {
                $table->string('avion')->nullable()->after('compagnie');
            }

            // Ajoute les colonnes manquantes si elles n'existent pas déjà
            if (!Schema::hasColumn('vols', 'ville_depart')) {
                $table->string('ville_depart')->nullable()->after('destination');
            }
            if (!Schema::hasColumn('vols', 'ville_arrivee')) {
                $table->string('ville_arrivee')->nullable()->after('ville_depart');
            }
            if (!Schema::hasColumn('vols', 'ville_depart_iata')) {
                $table->string('ville_depart_iata', 3)->nullable()->after('ville_arrivee');
            }
            if (!Schema::hasColumn('vols', 'ville_arrivee_iata')) {
                $table->string('ville_arrivee_iata', 3)->nullable()->after('ville_depart_iata');
            }
            if (!Schema::hasColumn('vols', 'type_vol')) {
                $table->string('type_vol')->default('direct')->after('statut');
            }
            if (!Schema::hasColumn('vols', 'duree_minutes')) {
                $table->integer('duree_minutes')->nullable()->after('type_vol');
            }
            if (!Schema::hasColumn('vols', 'compagnie')) {
                $table->string('compagnie')->nullable()->after('numero_vol');
            }
            if (!Schema::hasColumn('vols', 'inclusions')) {
                $table->json('inclusions')->nullable();
            }
            if (!Schema::hasColumn('vols', 'escales')) {
                $table->json('escales')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('vols', function (Blueprint $table) {
            $table->dropColumn(['avion']);
        });
    }
};