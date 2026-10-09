<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promos', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->decimal('valeur', 10, 2); // Montant de la réduction ou pourcentage
            $table->string('type')->default('pourcentage'); // pourcentage ou fixe
            $table->date('date_debut');
            $table->date('date_fin');
            $table->integer('utilisation_max')->nullable(); // Nombre max d'utilisations globales
            $table->integer('utilisations_count')->default(0); // Suivi du nombre d'utilisations
            $table->boolean('actif')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promos');
    }
};
