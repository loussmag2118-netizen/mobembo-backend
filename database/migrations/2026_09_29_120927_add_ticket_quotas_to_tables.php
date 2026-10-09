<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('planification_voyages', function (Blueprint $table) {
            // Nombre total de places / tickets disponibles pour ce voyage/jour
            $table->integer('nombre_places_total')->default(50);
            
            // Nombre de places restantes (mise à jour automatique à chaque vente)
            $table->integer('nombre_places_restantes')->default(50);
        });
    }

    public function down(): void
    {
        Schema::table('planification_voyages', function (Blueprint $table) {
            $table->dropColumn(['nombre_places_total', 'nombre_places_restantes']);
        });
    }
};

