<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trajets', function (Blueprint $table) {
            if (Schema::hasColumn('trajets', 'numero_bus')) {
                $table->string('numero_bus', 50)->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('trajets', function (Blueprint $table) {
            if (Schema::hasColumn('trajets', 'numero_bus')) {
                $table->string('numero_bus', 50)->nullable(false)->change();
            }
        });
    }
};