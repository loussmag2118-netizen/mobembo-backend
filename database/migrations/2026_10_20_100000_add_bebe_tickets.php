<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bébé (0 à 3 ans) voyageant sur les genoux d'un adulte : ses informations
 * figurent sur le billet de l'adulte, qui paie en plus une assurance.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->string('bebe_nom', 150)->nullable()->after('age_passager');
            $table->string('bebe_prenom', 150)->nullable()->after('bebe_nom');
            $table->unsignedTinyInteger('bebe_age')->nullable()->after('bebe_prenom');
            $table->decimal('assurance_bebe', 10, 2)->default(0)->after('bebe_age');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn(['bebe_nom', 'bebe_prenom', 'bebe_age', 'assurance_bebe']);
        });
    }
};
