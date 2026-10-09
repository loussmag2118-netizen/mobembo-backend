<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Ajouter la colonne
        Schema::table('planification_voyages', function (Blueprint $table) {
            if (!Schema::hasColumn('planification_voyages', 'agence_id')) {
                $table->unsignedBigInteger('agence_id')->nullable()->after('id');
            }
        });

        // 2. Remplir agence_id à partir du trajet_id associé
        DB::statement('
            UPDATE planification_voyages pv
            INNER JOIN trajets t ON pv.trajet_id = t.id
            SET pv.agence_id = t.agence_id
            WHERE pv.agence_id IS NULL
        ');
    }

    public function down(): void
    {
        Schema::table('planification_voyages', function (Blueprint $table) {
            $table->dropColumn('agence_id');
        });
    }
};