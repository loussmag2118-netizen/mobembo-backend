<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            if (!Schema::hasColumn('tickets', 'nom_passager')) {
                $table->string('nom_passager', 150)->nullable();
            }
            if (!Schema::hasColumn('tickets', 'prenom_passager')) {
                $table->string('prenom_passager', 150)->nullable();
            }
            if (!Schema::hasColumn('tickets', 'telephone_passager')) {
                $table->string('telephone_passager', 30)->nullable();
            }
            if (!Schema::hasColumn('tickets', 'ville_arrivee')) {
                $table->string('ville_arrivee', 100)->nullable();
            }
            if (!Schema::hasColumn('tickets', 'quartier')) {
                $table->string('quartier', 100)->nullable();
            }
            if (!Schema::hasColumn('tickets', 'numero_bus')) {
                $table->string('numero_bus', 50)->nullable();
            }
            if (!Schema::hasColumn('tickets', 'nom_trajet')) {
                $table->string('nom_trajet', 255)->nullable();
            }
            if (!Schema::hasColumn('tickets', 'horaire')) {
                $table->string('horaire', 10)->nullable();
            }
            if (!Schema::hasColumn('tickets', 'nom_vague')) {
                $table->string('nom_vague', 100)->nullable();
            }
            if (!Schema::hasColumn('tickets', 'mode_paiement')) {
                $table->string('mode_paiement', 20)->nullable();
            }
            if (!Schema::hasColumn('tickets', 'date_paiement')) {
                $table->dateTime('date_paiement')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn([
                'nom_passager', 'prenom_passager', 'telephone_passager',
                'ville_arrivee', 'quartier', 'numero_bus', 'nom_trajet',
                'horaire', 'nom_vague', 'mode_paiement', 'date_paiement',
            ]);
        });
    }
};