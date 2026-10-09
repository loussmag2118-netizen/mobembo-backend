<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vols', function (Blueprint $table) {
            // Ajouter les colonnes manquantes
            if (!Schema::hasColumn('vols', 'compagnie')) {
                $table->string('compagnie', 100)->nullable();
            }
            if (!Schema::hasColumn('vols', 'avion')) {
                $table->string('avion', 100)->nullable();
            }
            if (!Schema::hasColumn('vols', 'ville_depart')) {
                $table->string('ville_depart', 100)->nullable();
            }
            if (!Schema::hasColumn('vols', 'ville_arrivee')) {
                $table->string('ville_arrivee', 100)->nullable();
            }
            if (!Schema::hasColumn('vols', 'ville_depart_iata')) {
                $table->char('ville_depart_iata', 3)->nullable();
            }
            if (!Schema::hasColumn('vols', 'ville_arrivee_iata')) {
                $table->char('ville_arrivee_iata', 3)->nullable();
            }
            if (!Schema::hasColumn('vols', 'type_vol')) {
                $table->string('type_vol', 20)->default('direct');
            }
            if (!Schema::hasColumn('vols', 'duree_minutes')) {
                $table->integer('duree_minutes')->nullable();
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
            $table->dropColumn([
                'compagnie',
                'avion',
                'ville_depart',
                'ville_arrivee',
                'ville_depart_iata',
                'ville_arrivee_iata',
                'type_vol',
                'duree_minutes',
                'inclusions',
                'escales',
            ]);
        });
    }
};