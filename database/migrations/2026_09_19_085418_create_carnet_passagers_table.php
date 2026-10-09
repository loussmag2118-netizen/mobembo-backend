<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carnet_passagers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('canadian_user_id')
                  ->constrained('canadian_users')
                  ->cascadeOnDelete();

            $table->string('nom', 150);
            $table->string('prenom', 150);
            $table->date('date_naissance')->nullable();
            $table->string('numero_passeport', 50)->nullable();
            $table->string('nationalite', 100)->default('Congo');
            $table->string('type', 20)->default('adulte');

            $table->timestamps();

            // Un même user ne peut pas avoir 2 fois le même passager
            $table->unique(['canadian_user_id', 'nom', 'prenom', 'date_naissance'], 'unique_passager_par_user');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carnet_passagers');
    }
};