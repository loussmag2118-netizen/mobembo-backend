<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            // Gare de départ (liée à l'agence et à l'agent)
            $table->foreignId('gare_depart_id')
                  ->nullable()
                  ->after('vol_id')
                  ->constrained('gares')
                  ->nullOnDelete();

            // Gare d'arrivée
            $table->foreignId('gare_arrivee_id')
                  ->nullable()
                  ->after('gare_depart_id')
                  ->constrained('gares')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropForeign(['gare_depart_id']);
            $table->dropForeign(['gare_arrivee_id']);
            $table->dropColumn(['gare_depart_id', 'gare_arrivee_id']);
        });
    }
};
