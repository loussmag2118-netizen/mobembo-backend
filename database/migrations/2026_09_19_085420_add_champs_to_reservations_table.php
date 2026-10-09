<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            if (!Schema::hasColumn('reservations', 'canadian_user_id')) {
                $table->foreignId('canadian_user_id')
                      ->nullable()
                      ->after('user_id')
                      ->constrained('canadian_users')
                      ->nullOnDelete();
            }
            if (!Schema::hasColumn('reservations', 'type_reservation')) {
                $table->string('type_reservation', 20)->default('aller_simple')->after('vol_id'); // aller_simple | aller_retour
            }
            if (!Schema::hasColumn('reservations', 'vol_retour_id')) {
                $table->foreignId('vol_retour_id')
                      ->nullable()
                      ->after('type_reservation')
                      ->constrained('vols')
                      ->nullOnDelete();
            }
            if (!Schema::hasColumn('reservations', 'code_pnr')) {
                $table->string('code_pnr', 10)->nullable()->unique()->after('reference');
            }
            if (!Schema::hasColumn('reservations', 'classe')) {
                $table->string('classe', 30)->default('economique')->after('nombre_places');
            }
            if (!Schema::hasColumn('reservations', 'contact_telephone')) {
                $table->string('contact_telephone', 30)->nullable()->after('telephone');
            }
            if (!Schema::hasColumn('reservations', 'contact_email')) {
                $table->string('contact_email', 150)->nullable()->after('contact_telephone');
            }
            if (!Schema::hasColumn('reservations', 'prix_billet')) {
                $table->decimal('prix_billet', 10, 2)->default(0)->after('montant_total');
            }
            if (!Schema::hasColumn('reservations', 'prix_extras')) {
                $table->decimal('prix_extras', 10, 2)->default(0)->after('prix_billet');
            }
            if (!Schema::hasColumn('reservations', 'date_paiement')) {
                $table->timestamp('date_paiement')->nullable()->after('statut');
            }
            if (!Schema::hasColumn('reservations', 'methode_paiement')) {
                $table->string('methode_paiement', 30)->nullable()->after('date_paiement');
            }
            if (!Schema::hasColumn('reservations', 'reference_paiement')) {
                $table->string('reference_paiement', 100)->nullable()->after('methode_paiement');
            }
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropForeign(['canadian_user_id']);
            $table->dropForeign(['vol_retour_id']);
            $table->dropColumn([
                'canadian_user_id', 'type_reservation', 'vol_retour_id', 'code_pnr',
                'classe', 'contact_telephone', 'contact_email',
                'prix_billet', 'prix_extras', 'date_paiement',
                'methode_paiement', 'reference_paiement',
            ]);
        });
    }
};