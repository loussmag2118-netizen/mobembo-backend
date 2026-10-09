<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('classes_tarifaires', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();           // economique, affaires, premiere
            $table->string('nom', 100);                     // Économique, Affaires, Première
            $table->decimal('multiplicateur', 4, 2)->default(1.00); // 1.00, 2.50, 3.00
            $table->text('description')->nullable();
            $table->integer('ordre')->default(0);
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });

        // Seed par défaut
        DB::table('classes_tarifaires')->insert([
            ['code' => 'economique', 'nom' => 'Économique',  'multiplicateur' => 1.00, 'ordre' => 1, 'actif' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'affaires',   'nom' => 'Affaires',    'multiplicateur' => 2.50, 'ordre' => 2, 'actif' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'premiere',   'nom' => 'Première',    'multiplicateur' => 3.50, 'ordre' => 3, 'actif' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('classes_tarifaires');
    }
};