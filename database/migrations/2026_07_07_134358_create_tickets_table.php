<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();

            // ═══════════════════════════════════════════════════
            // LIAISONS
            // ═══════════════════════════════════════════════════
            $table->foreignId('agence_id')
                  ->constrained('agences')
                  ->cascadeOnDelete();

            $table->foreignId('planification_voyage_id')
                  ->nullable()
                  ->constrained('planification_voyages')
                  ->nullOnDelete();

            $table->foreignId('agent_vendeur_id')
                  ->nullable()
                  ->constrained('utilisateurs')
                  ->nullOnDelete();

            $table->foreignId('agent_controleur_id')
                  ->nullable()
                  ->constrained('utilisateurs')
                  ->nullOnDelete();

            $table->foreignId('utilisateur_id')
                  ->nullable()
                  ->constrained('utilisateurs')
                  ->nullOnDelete();

            $table->foreignId('gare_arrivee_id')
                  ->nullable()
                  ->constrained('gares')
                  ->nullOnDelete();

            // ═══════════════════════════════════════════════════
            // IDENTIFIANTS
            // ═══════════════════════════════════════════════════
            $table->string('reference_ticket', 100)->unique();
            $table->string('qr_code_token', 255)->unique();

            // ═══════════════════════════════════════════════════
            // PASSAGER
            // ═══════════════════════════════════════════════════
            $table->string('nom_passager', 150);
            $table->string('prenom_passager', 150)->nullable(); // ⭐ AJOUT
            $table->string('telephone_passager', 30)->nullable();

            // ═══════════════════════════════════════════════════
            // DESTINATION (figée au moment du paiement)
            // ═══════════════════════════════════════════════════
            $table->string('ville_depart', 100)->nullable();    // ⭐ AJOUT
            $table->string('ville_arrivee', 100)->nullable();
            $table->string('quartier', 100)->nullable();
            $table->string('nom_trajet', 200)->nullable();      // ⭐ AJOUT
            $table->string('numero_bus', 50)->nullable();       // ⭐ AJOUT
            $table->string('horaire', 10)->nullable();          // ⭐ AJOUT
            $table->string('nom_vague', 100)->nullable();       // ⭐ AJOUT

            // ═══════════════════════════════════════════════════
            // DÉTAILS
            // ═══════════════════════════════════════════════════
            $table->integer('nombre_places_reservees')->default(1);
            $table->decimal('montant_total', 10, 2);
            $table->decimal('prix', 10, 2)->nullable();         // ⭐ AJOUT

            // ═══════════════════════════════════════════════════
            // STATUTS
            // ═══════════════════════════════════════════════════
            $table->enum('statut_paiement',
                ['En_Attente', 'Paye', 'Echoue', 'Rembourse'])
                ->default('En_Attente');

            $table->enum('statut_embarquement',
                ['En_Attente', 'Present', 'Absent'])
                ->default('En_Attente');

            $table->boolean('present')->nullable();             // ⭐ AJOUT

            // ═══════════════════════════════════════════════════
            // PAIEMENT
            // ═══════════════════════════════════════════════════
            $table->string('mode_paiement', 20)->nullable();
            $table->string('telephone_paiement', 30)->nullable();   // ⭐ AJOUT
            $table->string('reference_paiement', 100)->nullable();  // ⭐ AJOUT
            $table->dateTime('date_paiement')->nullable();

            // ═══════════════════════════════════════════════════
            // RÉSERVATION
            // ═══════════════════════════════════════════════════
            $table->dateTime('date_reservation')->useCurrent();

            // ═══════════════════════════════════════════════════
            // TIMESTAMPS + SOFT DELETE
            // ═══════════════════════════════════════════════════
            $table->timestamps();
            $table->softDeletes();                              // ⭐ AJOUT

            // ═══════════════════════════════════════════════════
            // INDEX
            // ═══════════════════════════════════════════════════
            $table->index('agence_id');
            $table->index('planification_voyage_id');
            $table->index('statut_paiement');
            $table->index('statut_embarquement');
            $table->index('date_paiement');
        });

        // Contrainte d'intégrité
        DB::statement('ALTER TABLE tickets ADD CONSTRAINT chk_nombre_places CHECK (nombre_places_reservees > 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};