<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paiements', function (Blueprint $table) {
            $table->id();
            $table->string('reference_transaction', 100)->nullable();
            $table->string('nom_client', 150)->nullable();
            $table->string('telephone_client', 30)->nullable();
            $table->string('methode', 20)->default('MTN');
            $table->decimal('montant', 10, 2)->default(0);
            $table->string('statut', 20)->default('En_Attente');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paiements');
    }
};