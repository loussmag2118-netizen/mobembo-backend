<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Deuxième couleur de l'identité de l'agence (la première est `couleur`).
 * Les deux habillent son administration et son accueil dans l'application.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agences', function (Blueprint $table) {
            $table->string('couleur_secondaire', 7)->nullable()->after('couleur');
        });
    }

    public function down(): void
    {
        Schema::table('agences', function (Blueprint $table) {
            $table->dropColumn('couleur_secondaire');
        });
    }
};
