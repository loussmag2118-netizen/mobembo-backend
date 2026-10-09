<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planification_voyages', function (Blueprint $table) {
            $table->id();

            // ⭐ Liaison directe à l'agence (->after('id') a été retiré)
            $table->foreignId('agence_id')
                  ->constrained('agences')
                  ->onDelete('cascade');

            // Relations principales
            $table->foreignId('trajet_id')
                  ->constrained('trajets')
                  ->onDelete('cascade');

            $table->foreignId('bus_id')
                  ->constrained('bus')
                  ->onDelete('cascade');

            $table->foreignId('vague_depart_id')
                  ->constrained('vague_departs')
                  ->onDelete('cascade');

            // Données du voyage
            $table->date('date_voyage');
            $table->integer('nombre_tickets_total')->nullable(); // ⭐ Quota total de tickets configuré
            $table->integer('places_disponibles')->default(0);

            $table->enum('statut_voyage',
                ['Programmation', 'En cours', 'Terminé', 'Annulé'])
                ->default('Programmation');

            $table->timestamps();

            // ⭐ Unicité : un bus ne peut pas être utilisé 2 fois à la même date + même horaire
            $table->unique(
                ['bus_id', 'date_voyage', 'vague_depart_id'],
                'uq_planification_bus_date'
            );

            // ⭐ Index de performance (colonnes fréquemment filtrées)
            $table->index('date_voyage');    // tri + filtres "à venir"
            $table->index('statut_voyage');  // filtres par statut
        });

        // ⭐ Contrainte CHECK (MySQL 8.0.16+)
        if ($this->supportsCheckConstraints()) {
            DB::statement('ALTER TABLE planification_voyages 
                ADD CONSTRAINT chk_places_disponibles 
                CHECK (places_disponibles >= 0)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('planification_voyages');
    }

    /**
     * Vérifie si MySQL supporte les contraintes CHECK (8.0.16+).
     */
    private function supportsCheckConstraints(): bool
    {
        try {
            $version = DB::selectOne('SELECT VERSION() as v')?->v ?? '0';
            return version_compare($version, '8.0.16', '>=');
        } catch (\Throwable $e) {
            return false;
        }
    }
};