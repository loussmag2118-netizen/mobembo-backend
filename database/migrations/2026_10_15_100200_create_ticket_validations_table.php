<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Historique des contrôles de billets (scan du QR par un agent),
 * y compris les tentatives refusées (non payé, déjà utilisé…).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_validations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->nullable()->constrained('tickets')->nullOnDelete();
            $table->foreignId('agent_id')->nullable()->constrained('utilisateurs')->nullOnDelete();
            $table->unsignedBigInteger('agence_id')->nullable()->index();
            // VALIDE, DEJA_UTILISE, NON_PAYE, AUTRE_AGENCE, BILLET_TEST, INTROUVABLE
            $table->string('resultat', 30)->index();
            $table->string('code_scanne', 120)->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_validations');
    }
};
