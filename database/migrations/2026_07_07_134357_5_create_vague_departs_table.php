<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vague_departs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agence_id')->constrained('agences')->onDelete('cascade');

            // ⭐ Lien vers le trajet (sans ->after())
            $table->foreignId('trajet_id')
                  ->nullable()
                  ->constrained('trajets')
                  ->onDelete('cascade');

            $table->time('heure_depart');
            $table->string('nom_vague', 100);
            $table->timestamps();
            $table->softDeletes();

            // Unicité : pas de doublon horaire PAR TRAJET
            $table->unique(
                ['trajet_id', 'heure_depart'],
                'uq_vague_trajet'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vague_departs');
    }
};