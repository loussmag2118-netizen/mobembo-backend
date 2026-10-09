<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agence_id')->constrained('agences')->onDelete('cascade');
            $table->string('nom_gare', 100);
            $table->string('ville', 100);
            $table->string('quartier', 100)->nullable(); // ⬅️ NOUVEAU
            $table->timestamps();

            $table->unique(['agence_id', 'nom_gare', 'ville'], 'uq_gare_agence');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gares');
    }
};