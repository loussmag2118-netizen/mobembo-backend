<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 50)->unique();
            
            // ⬇️ Utilise 'utilisateurs' au lieu de 'users'
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('utilisateurs')
                  ->cascadeOnDelete();
            
            $table->foreignId('vol_id')
                  ->constrained('vols')
                  ->cascadeOnDelete();
            
            // Infos passager (dénormalisées pour l'affichage)
            $table->string('nom_passager', 150)->nullable();
            $table->string('telephone', 30)->nullable();
            
            $table->integer('nombre_places')->default(1);
            $table->decimal('montant_total', 10, 2);
            
            // ⬇️ Majuscules pour matcher le contrôleur
            $table->string('statut', 30)->default('En_Attente');
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};