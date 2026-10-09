<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('utilisateurs', function (Blueprint $table) {
            $table->unsignedBigInteger('gare_id')->nullable()->after('agence_id');
            $table->foreign('gare_id')->references('id')->on('gares')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('utilisateurs', function (Blueprint $table) {
            $table->dropForeign(['gare_id']);
            $table->dropColumn('gare_id');
        });
    }
};
