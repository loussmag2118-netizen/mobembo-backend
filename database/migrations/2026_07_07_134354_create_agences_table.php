<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agences', function (Blueprint $table) {
            $table->id();
            $table->string('nom_agence', 100)->unique();
            $table->enum('statut', ['Actif', 'Bloqué'])->default('Actif');
            $table->date('date_partenariat');
            $table->foreignId('superadmin_id')->constrained('super_admins');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agences');
    }
};