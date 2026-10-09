<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('extras', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();           // bagage_23kg, siege_hublot, assurance...
            $table->string('nom', 150);                     // Bagage soute 23kg
            $table->text('description')->nullable();
            $table->decimal('prix', 10, 2)->default(0);
            $table->string('categorie', 30)->default('autre'); // bagage | siege | assurance | repas | wifi
            $table->string('icone', 50)->nullable();        // fa-suitcase, fa-chair, fa-shield...
            $table->integer('ordre')->default(0);
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });

        // Seed des extras vus dans l'interface
        DB::table('extras')->insert([
            ['code' => 'bagage_23kg',    'nom' => 'Bagage soute 23kg',   'prix' => 15000, 'categorie' => 'bagage',    'icone' => 'fa-suitcase', 'ordre' => 1, 'actif' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'bagage_32kg',    'nom' => 'Bagage 32kg',         'prix' => 25000, 'categorie' => 'bagage',    'icone' => 'fa-suitcase', 'ordre' => 2, 'actif' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'siege_hublot',   'nom' => 'Choix siège hublot',  'prix' => 5000,  'categorie' => 'siege',     'icone' => 'fa-chair',    'ordre' => 3, 'actif' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'assurance',      'nom' => 'Assurance voyage',    'prix' => 3500,  'categorie' => 'assurance', 'icone' => 'fa-shield',   'ordre' => 4, 'actif' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'repas',          'nom' => 'Repas à bord',        'prix' => 7000,  'categorie' => 'repas',     'icone' => 'fa-utensils', 'ordre' => 5, 'actif' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('extras');
    }
};