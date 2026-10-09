<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Table de log pour TOUTES les transactions Airtel Money.
     */
    public function up(): void
    {
        Schema::create('paiements_airtel', function (Blueprint $table) {
            $table->id();

            // Liaison
            $table->foreignId('agence_id')
                  ->nullable()
                  ->constrained('agences')
                  ->nullOnDelete();

            $table->foreignId('ticket_id')
                  ->nullable()
                  ->constrained('tickets')
                  ->nullOnDelete();

            // Références Airtel
            $table->string('reference_id', 100)->unique();      // ID interne
            $table->string('airtel_money_id', 100)->nullable(); // ID retourné par Airtel
            $table->string('transaction_id', 100)->nullable();  // ID de la transaction

            // Montant
            $table->decimal('amount', 10, 2);
            $table->string('currency', 10)->default('XAF');
            $table->string('country', 5)->default('CG');

            // Client
            $table->string('payer_msisdn', 30);

            // Statut Airtel
            $table->enum('status', [
                'PENDING',      // en cours
                'SUCCESS',      // payé
                'FAILED',       // échec
                'AMBIGUOUS',    // incertain
                'CANCELLED',    // annulé
            ])->default('PENDING');

            // Réponse brute Airtel (JSON complet)
            $table->json('airtel_response')->nullable();

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
        Schema::dropIfExists('paiements_airtel');
    }
};