<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Table de log pour TOUTES les transactions MTN MoMo.
     * Une ligne par tentative de paiement (succès, échec, en attente).
     */
    public function up(): void
    {
        Schema::create('paiements_mtn_momo', function (Blueprint $table) {
            $table->id();

            // Liaison avec l'agence & le ticket éventuel
            $table->foreignId('agence_id')
                  ->nullable()
                  ->constrained('agences')
                  ->nullOnDelete();

            $table->foreignId('ticket_id')
                  ->nullable()
                  ->constrained('tickets')
                  ->nullOnDelete();

            // Références internes MTN
            $table->string('reference_id', 100)->unique();      // X-Reference-Id envoyé à MTN
            $table->string('external_id', 100)->nullable();     // externalId
            $table->string('financial_transaction_id', 100)->nullable(); // retourné par MTN

            // Montant
            $table->decimal('amount', 10, 2);
            $table->string('currency', 10)->default('XAF');

            // Client
            $table->string('payer_msisdn', 30);                 // numéro qui paie
            $table->string('payer_message', 255)->nullable();
            $table->string('payee_note', 255)->nullable();

            // Statut MTN
            $table->enum('status', [
                'PENDING',      // en cours
                'SUCCESSFUL',   // payé
                'FAILED',       // échec
                'REJECTED',     // rejeté par client
                'TIMEOUT',      // expiré
            ])->default('PENDING');

            // Réponse brute MTN (JSON complet)
            $table->json('mtn_response')->nullable();

            // Environnement
            $table->enum('environment', ['sandbox', 'production'])->default('sandbox');
            $table->boolean('simulation')->default(false);

            // Timestamps
            $table->timestamps();

            // Index
            $table->index('status');
            $table->index('payer_msisdn');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paiements_mtn_momo');
    }
};