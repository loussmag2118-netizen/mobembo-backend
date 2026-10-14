<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Informations affichées au client lors de la recherche, saisies par
 * l'agence : durée estimée, bagages autorisés, conditions du voyage.
 * Toutes facultatives : rien n'est affiché si elles ne sont pas renseignées.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trajets', function (Blueprint $table) {
            $table->unsignedSmallInteger('duree_minutes')->nullable()->after('bus_id');
            $table->string('bagages_autorises', 255)->nullable()->after('duree_minutes');
            $table->text('conditions_voyage')->nullable()->after('bagages_autorises');
        });
    }

    public function down(): void
    {
        Schema::table('trajets', function (Blueprint $table) {
            $table->dropColumn(['duree_minutes', 'bagages_autorises', 'conditions_voyage']);
        });
    }
};
