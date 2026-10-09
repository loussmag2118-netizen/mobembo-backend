<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('paiements', function (Blueprint $table) {
            if (!Schema::hasColumn('paiements', 'reference_transaction')) {
                $table->string('reference_transaction', 100)->nullable()->after('id');
            }
            if (!Schema::hasColumn('paiements', 'nom_client')) {
                $table->string('nom_client', 150)->nullable();
            }
            if (!Schema::hasColumn('paiements', 'telephone_client')) {
                $table->string('telephone_client', 30)->nullable();
            }
            if (!Schema::hasColumn('paiements', 'methode')) {
                $table->string('methode', 20)->default('MTN');
            }
            if (!Schema::hasColumn('paiements', 'montant')) {
                $table->decimal('montant', 10, 2)->default(0);
            }
            if (!Schema::hasColumn('paiements', 'statut')) {
                $table->string('statut', 20)->default('En_Attente');
            }
        });
    }

    public function down(): void
    {
        // Rien à faire
    }
};