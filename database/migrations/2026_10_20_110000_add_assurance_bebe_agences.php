<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Montant de l'assurance d'un bébé (0 à 3 ans) fixé par chaque compagnie.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agences', function (Blueprint $table) {
            $table->unsignedInteger('assurance_bebe')->default(5000)->after('age_max_enfant');
        });
    }

    public function down(): void
    {
        Schema::table('agences', fn (Blueprint $table) => $table->dropColumn('assurance_bebe'));
    }
};
