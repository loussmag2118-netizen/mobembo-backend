<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Compagnies aériennes partenaires.
 *
 * Un partenaire est une agence de type « bus » ou « aerien » : même
 * inscription, même identité visuelle, mêmes administrateurs (table
 * utilisateurs). Toutes les données aériennes sont rattachées à leur
 * compagnie (agence_id) ; les codes (vol, promo, classe, extra) ne sont
 * uniques qu'au sein d'une compagnie.
 *
 * Canadian Airways devient la première compagnie aérienne.
 */
return new class extends Migration
{
    /** Tables aériennes rattachées à une compagnie */
    private const TABLES = [
        'vols', 'flottes', 'reservations', 'paiements', 'promos',
        'classes_tarifaires', 'extras', 'parametres', 'notifications',
    ];

    /** Index uniques globaux remplacés par des index par compagnie : table => [ancien index, colonne] */
    private const UNIQUES = [
        'vols'               => ['vols_numero_vol_unique', 'numero_vol'],
        'promos'             => ['promos_code_unique', 'code'],
        'classes_tarifaires' => ['classes_tarifaires_code_unique', 'code'],
        'extras'             => ['extras_code_unique', 'code'],
        'parametres'         => ['parametres_cle_unique', 'cle'],
    ];

    public function up(): void
    {
        Schema::table('agences', function (Blueprint $table) {
            $table->string('type_transport', 10)->default('bus')->index()->after('nom_agence');
        });

        foreach (self::TABLES as $nom) {
            Schema::table($nom, function (Blueprint $table) {
                $table->foreignId('agence_id')->nullable()->after('id')->constrained('agences')->nullOnDelete();
            });
        }

        foreach (self::UNIQUES as $nom => [$index, $colonne]) {
            Schema::table($nom, function (Blueprint $table) use ($index, $colonne) {
                $table->dropUnique($index);
                $table->unique(['agence_id', $colonne]);
            });
        }

        // Flotte : colonnes attendues par l'administration
        Schema::table('flottes', function (Blueprint $table) {
            $table->string('modele', 100)->nullable()->after('nom_appareil');
            $table->unsignedSmallInteger('annee')->nullable()->after('capacite_places');
        });

        // Vols : capacité mise en vente (le restant se recalcule à partir des réservations)
        Schema::table('vols', function (Blueprint $table) {
            $table->unsignedInteger('places_totales')->nullable()->after('places_disponibles');
            $table->foreignId('flotte_id')->nullable()->after('avion')->constrained('flottes')->nullOnDelete();
        });
        DB::table('vols')->whereNull('places_totales')->update(['places_totales' => DB::raw('places_disponibles')]);

        // Réservations : délai de paiement et lien avec le paiement opérateur
        Schema::table('reservations', function (Blueprint $table) {
            $table->timestamp('expire_le')->nullable()->after('statut');
        });
        Schema::table('paiements', function (Blueprint $table) {
            $table->foreignId('reservation_id')->nullable()->after('agence_id')->constrained('reservations')->nullOnDelete();
        });
        foreach (['paiements_mtn_momo', 'paiements_airtel'] as $nom) {
            Schema::table($nom, function (Blueprint $table) {
                $table->unsignedBigInteger('reservation_id')->nullable()->after('ticket_id')->index();
            });
        }

        // ---- Canadian Airways, première compagnie aérienne
        $superAdmin = DB::table('super_admins')->orderBy('id')->value('id');
        if (! $superAdmin) {
            return;   // base vide (tests) : rien à reprendre
        }

        $canadian = DB::table('agences')->where('nom_agence', 'Canadian Airways')->value('id')
            ?? DB::table('agences')->insertGetId([
                'nom_agence'       => 'Canadian Airways',
                'type_transport'   => 'aerien',
                'statut'           => 'Actif',
                'couleur'          => '#C62828',
                'slogan'           => 'Le ciel à portée de main.',
                'date_partenariat' => now()->toDateString(),
                'superadmin_id'    => $superAdmin,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);
        DB::table('agences')->where('id', $canadian)->update(['type_transport' => 'aerien']);

        foreach (self::TABLES as $nom) {
            $requete = DB::table($nom)->whereNull('agence_id');
            // Notifications : seules celles des clients aériens
            if ($nom === 'notifications') {
                $requete->whereNotNull('canadian_user_id');
            }
            $requete->update(['agence_id' => $canadian]);
        }
    }

    public function down(): void
    {
        foreach (['paiements_mtn_momo', 'paiements_airtel'] as $nom) {
            Schema::table($nom, fn (Blueprint $table) => $table->dropColumn('reservation_id'));
        }
        Schema::table('paiements', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reservation_id');
        });
        Schema::table('reservations', fn (Blueprint $table) => $table->dropColumn('expire_le'));
        Schema::table('flottes', fn (Blueprint $table) => $table->dropColumn(['modele', 'annee']));
        Schema::table('vols', function (Blueprint $table) {
            $table->dropConstrainedForeignId('flotte_id');
            $table->dropColumn('places_totales');
        });

        // La clé étrangère s'appuie sur l'index composite : elle part d'abord
        foreach (self::TABLES as $nom) {
            Schema::table($nom, fn (Blueprint $table) => $table->dropForeign(['agence_id']));
        }
        foreach (self::UNIQUES as $nom => [$index, $colonne]) {
            Schema::table($nom, function (Blueprint $table) use ($index, $colonne) {
                $table->dropUnique(['agence_id', $colonne]);
                $table->unique($colonne, $index);
            });
        }
        foreach (self::TABLES as $nom) {
            Schema::table($nom, fn (Blueprint $table) => $table->dropColumn('agence_id'));
        }
        Schema::table('agences', function (Blueprint $table) {
            $table->dropIndex(['type_transport']);
            $table->dropColumn('type_transport');
        });
    }
};
