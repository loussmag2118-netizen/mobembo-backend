<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trajets', function (Blueprint $table) {
            $table->id();

            $table->foreignId('agence_id')
                  ->constrained('agences')
                  ->onDelete('cascade');

            // Villes (dénormalisées pour affichage rapide)
            $table->string('ville_depart', 100);
            $table->string('ville_arrivee', 100);

            // Gares (FK)
            $table->foreignId('gare_depart_id')
                  ->constrained('gares')
                  ->onDelete('cascade');

            $table->foreignId('gare_arrivee_id')
                  ->constrained('gares')
                  ->onDelete('cascade');

            // ⭐ Bus assigné (nullable — un trajet peut ne pas avoir de bus fixe)
            $table->foreignId('bus_id')
                  ->nullable()
                  ->constrained('bus')
                  ->nullOnDelete();

            // Prix de base du trajet (optionnel)
            $table->decimal('prix', 10, 2)->nullable();

            $table->timestamps();
            $table->softDeletes(); // ⭐ pour le destroy/restore

            // Contraintes
            $table->unique(
                ['agence_id', 'gare_depart_id', 'gare_arrivee_id'],
                'uq_trajet_agence'
            );

            $table->index('bus_id'); // ⭐ accélère les jointures bus ↔ trajet
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trajets');
    }
};
