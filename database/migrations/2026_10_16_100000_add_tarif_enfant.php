<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tarif enfant :
 *  - prix_enfant sur chaque départ (null = même prix que l'adulte) ;
 *  - âge maximum « enfant » propre à chaque agence (12 ans par défaut) ;
 *  - catégorie et âge du passager sur chaque billet (contrôle à l'embarquement).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('planification_voyages', function (Blueprint $table) {
            $table->decimal('prix_enfant', 10, 2)->nullable()->after('prix');
        });

        Schema::table('agences', function (Blueprint $table) {
            $table->unsignedTinyInteger('age_max_enfant')->default(12)->after('statut');
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->string('categorie_passager', 10)->default('Adulte')->after('telephone_passager');
            $table->unsignedTinyInteger('age_passager')->nullable()->after('categorie_passager');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn(['categorie_passager', 'age_passager']);
        });
        Schema::table('agences', function (Blueprint $table) {
            $table->dropColumn('age_max_enfant');
        });
        Schema::table('planification_voyages', function (Blueprint $table) {
            $table->dropColumn('prix_enfant');
        });
    }
};
