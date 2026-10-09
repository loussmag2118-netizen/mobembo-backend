<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('villes', function (Blueprint $table) {
            $table->id();
            $table->string('nom', 100)->unique();
            $table->char('code_iata', 3)->unique();       // BZV, PNR, YUL, DKR...
            $table->string('pays', 100)->default('Congo');
            $table->string('drapeau', 10)->nullable();    // emoji 🇨🇬 ou chemin image
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('villes');
    }
};