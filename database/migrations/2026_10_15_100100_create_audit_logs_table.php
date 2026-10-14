<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Journal d'audit des opérations sensibles (connexions, paiements,
 * modifications de l'administration, contrôles de billets…).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('acteur_type', 30)->default('systeme');   // utilisateur, superadmin, canadian, systeme
            $table->unsignedBigInteger('acteur_id')->nullable();
            $table->unsignedBigInteger('agence_id')->nullable()->index();
            $table->string('action', 80)->index();                   // ex. paiement.confirme, admin.trajet.supprime
            $table->string('cible_type', 80)->nullable();
            $table->unsignedBigInteger('cible_id')->nullable();
            $table->json('details')->nullable();
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamp('created_at')->useCurrent()->index();

            $table->index(['acteur_type', 'acteur_id']);
            $table->index(['cible_type', 'cible_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
