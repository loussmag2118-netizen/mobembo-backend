<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flottes', function (Blueprint $table) {
            $table->id();
            $table->string('nom_appareil'); // Ex: Boeing 737-800
            $table->string('immatriculation')->unique(); // Ex: TN-ABC
            $table->integer('capacite_places'); // Nombre de sièges
            $table->string('statut')->default('disponible'); // disponible, en_maintenance, en_vol
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flottes');
    }
};
