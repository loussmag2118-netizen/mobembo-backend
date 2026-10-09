<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('planification_voyages', function (Blueprint $table) {
            $table->decimal('prix', 10, 2)->nullable()->after('nombre_tickets_total');
        });
    }

    public function down(): void
    {
        Schema::table('planification_voyages', function (Blueprint $table) {
            $table->dropColumn('prix');
        });
    }
};
