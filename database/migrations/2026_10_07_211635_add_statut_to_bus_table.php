<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bus', function (Blueprint $table) {
            if (!Schema::hasColumn('bus', 'statut')) {
                // Valeurs possibles : 'Disponible', 'En service', 'Indisponible', 'Maintenance'
                $table->string('statut', 30)->default('Disponible')->after('capacite_totale');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bus', function (Blueprint $table) {
            $table->dropColumn('statut');
        });
    }
};