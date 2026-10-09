<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vols', function (Blueprint $table) {
            if (!Schema::hasColumn('vols', 'type_vol')) {
                $table->string('type_vol', 20)->default('direct')->after('statut'); // direct | escale
            }
            if (!Schema::hasColumn('vols', 'duree_minutes')) {
                $table->integer('duree_minutes')->nullable()->after('type_vol');
            }
            if (!Schema::hasColumn('vols', 'inclusions')) {
                // JSON : ["bagage_cabine", "repas", "wifi"]
                $table->json('inclusions')->nullable()->after('duree_minutes');
            }
            if (!Schema::hasColumn('vols', 'compagnie')) {
                $table->string('compagnie', 100)->default('Canadian Airways')->after('inclusions');
            }
            if (!Schema::hasColumn('vols', 'escales')) {
                $table->json('escales')->nullable()->after('compagnie'); // pour escale : ["Libreville", "Douala"]
            }
        });
    }

    public function down(): void
    {
        Schema::table('vols', function (Blueprint $table) {
            $table->dropColumn(['type_vol', 'duree_minutes', 'inclusions', 'compagnie', 'escales']);
        });
    }
};