<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trajets', function (Blueprint $table) {
            if (!Schema::hasColumn('trajets', 'ville_depart')) {
                $table->string('ville_depart', 100)->nullable()->after('agence_id');
            }
            if (!Schema::hasColumn('trajets', 'ville_arrivee')) {
                $table->string('ville_arrivee', 100)->nullable()->after('ville_depart');
            }
            if (!Schema::hasColumn('trajets', 'bus_id')) {
                $table->unsignedBigInteger('bus_id')->nullable()->after('gare_arrivee_id');
                $table->index('bus_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('trajets', function (Blueprint $table) {
            $table->dropIndex(['bus_id']);
            $table->dropColumn(['ville_depart', 'ville_arrivee', 'bus_id']);
        });
    }
};