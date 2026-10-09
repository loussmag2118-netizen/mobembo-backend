<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('planification_voyages', function (Blueprint $table) {
            if (!Schema::hasColumn('planification_voyages', 'nombre_tickets_total')) {
                $table->integer('nombre_tickets_total')->nullable()->after('date_voyage');
            }
        });
    }

    public function down(): void
    {
        Schema::table('planification_voyages', function (Blueprint $table) {
            if (Schema::hasColumn('planification_voyages', 'nombre_tickets_total')) {
                $table->dropColumn('nombre_tickets_total');
            }
        });
    }
};