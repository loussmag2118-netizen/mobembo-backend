<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('passagers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')
                  ->constrained('reservations')
                  ->cascadeOnDelete();

            $table->string('type', 20)->default('adulte');    // adulte | enfant | bebe
            $table->string('nom', 150);
            $table->string('prenom', 150);
            $table->date('date_naissance')->nullable();
            $table->string('numero_passeport', 50)->nullable();
            $table->string('nationalite', 100)->default('Congo');
            $table->string('telephone', 30)->nullable();      // optionnel par passager
            $table->string('email', 150)->nullable();
            $table->boolean('sauvegarde_carnet')->default(false); // a coché "enregistrer"

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('passagers');
    }
};