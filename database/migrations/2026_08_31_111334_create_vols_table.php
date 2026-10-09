<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Exécuter les migrations.
     */
    public function up(): void
    {
        Schema::create('vols', function (Blueprint $table) {
            $table->id();
            $table->string('numero_vol')->unique();
            $table->string('origine');
            $table->string('destination');
            $table->dateTime('date_depart');
            $table->dateTime('date_arrivee');
            $table->decimal('prix', 10, 2);
            $table->integer('places_disponibles');
            $table->string('statut')->default('programme'); // programmé, en_cours, terminé, annulé
            $table->timestamps();
            $table->softDeletes(); // Permet la suppression douce (corbeille)
        });
    }

    /**
     * Annuler les migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vols');
    }
};
