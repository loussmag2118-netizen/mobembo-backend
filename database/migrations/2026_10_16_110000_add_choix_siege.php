<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Choix du siège (activé agence par agence).
 *
 *  - agences.choix_siege : le client choisit son siège dans l'application ;
 *  - bus.disposition_sieges : sièges par rangée de part et d'autre du
 *    couloir (« 2-2 », « 2-1 », « 3-2 ») pour dessiner le plan du bus ;
 *  - tickets.numero_siege : siège attribué au billet.
 *
 * Garantie anti double-vente : siege_occupe ne vaut le numéro du siège que
 * pour un billet payé ou réservé (En_Attente). L'index unique
 * (départ, siege_occupe) empêche deux billets actifs sur le même siège,
 * même lors d'achats simultanés. Un billet échoué ou remboursé libère le
 * siège sans perdre l'historique de son numéro.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agences', function (Blueprint $table) {
            $table->boolean('choix_siege')->default(false)->after('age_max_enfant');
        });

        Schema::table('bus', function (Blueprint $table) {
            $table->string('disposition_sieges', 5)->default('2-2')->after('capacite_totale');
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->unsignedSmallInteger('numero_siege')->nullable()->after('age_passager');
            $table->unsignedSmallInteger('siege_occupe')->nullable()->after('numero_siege')
                ->storedAs("CASE WHEN statut_paiement IN ('Paye', 'En_Attente') THEN numero_siege ELSE NULL END");
            $table->unique(['planification_voyage_id', 'siege_occupe'], 'tickets_siege_unique_par_depart');
        });

        // Activé pour Nzoko Transport (demande de l'agence)
        DB::table('agences')->where('nom_agence', 'Nzoko Transport')->update(['choix_siege' => true]);
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropUnique('tickets_siege_unique_par_depart');
            $table->dropColumn(['siege_occupe', 'numero_siege']);
        });
        Schema::table('bus', function (Blueprint $table) {
            $table->dropColumn('disposition_sieges');
        });
        Schema::table('agences', function (Blueprint $table) {
            $table->dropColumn('choix_siege');
        });
    }
};
