<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('utilisateurs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('agence_id')
                  ->constrained('agences')
                  ->onDelete('cascade');

            $table->string('nom', 100);
            $table->string('prenom', 100);
            $table->string('email', 150)->nullable()->unique();

            // Format : 06XXXXXXX (9 chiffres, sans +242)
            $table->string('telephone', 20)->unique();

            $table->enum('role', ['Admin', 'Agent']);

            $table->string('mot_de_passe', 255); // bcrypt

            $table->string('code_acces_agent', 50)->nullable()->unique();

            // ⚠️ minuscules pour matcher le contrôleur (in:actif,suspendu)
            $table->enum('statut', ['actif', 'suspendu'])->default('actif');

            $table->boolean('compte_confirme')->default(false);

            $table->timestamps();
            $table->softDeletes();   // ← ajouté pour la corbeille (onlyTrashed / restore / forceDelete)
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('utilisateurs');
    }
};