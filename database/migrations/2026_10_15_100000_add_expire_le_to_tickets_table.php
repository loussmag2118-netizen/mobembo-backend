<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Réservation temporaire : un ticket « En_Attente » bloque sa place
 * jusqu'à expire_le. Passé ce délai sans paiement confirmé, il est
 * marqué « Echoue » et sa place est libérée (ReservationService).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dateTime('expire_le')->nullable()->after('date_reservation');

            // Comptage rapide des places occupées d'un départ
            $table->index(['planification_voyage_id', 'statut_paiement'], 'tickets_voyage_statut_index');
            // Recherche des réservations expirées
            $table->index(['statut_paiement', 'expire_le'], 'tickets_statut_expiration_index');
            // Regroupement des tickets d'un même paiement
            $table->index('reference_paiement', 'tickets_reference_paiement_index');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropIndex('tickets_reference_paiement_index');
            $table->dropIndex('tickets_statut_expiration_index');
            $table->dropIndex('tickets_voyage_statut_index');
            $table->dropColumn('expire_le');
        });
    }
};
