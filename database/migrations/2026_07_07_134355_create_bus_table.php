<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agence_id')
                  ->constrained('agences')
                  ->onDelete('cascade');

            $table->string('immatriculation', 50);
            $table->string('modele', 100);
            $table->integer('capacite_totale');
            $table->string('statut', 20)->default('Disponible'); // ⭐ NOUVEAU

            $table->timestamps();
            $table->softDeletes(); // ⭐ NOUVEAU

            // ⭐ Unicité composite : immatriculation unique PAR agence
            $table->unique(['agence_id', 'immatriculation'], 'bus_agence_immat_unique');

            // Index pour accélérer les requêtes filtrées par statut
            $table->index('statut');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bus');
    }
};