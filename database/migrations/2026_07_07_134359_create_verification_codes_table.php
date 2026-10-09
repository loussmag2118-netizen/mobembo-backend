<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('verification_codes', function (Blueprint $table) {
            $table->id();
            $table->string('identifiant', 100);           // téléphone ou email
            $table->string('canal', 20);                  // 'sms' ou 'email'
            $table->string('type_usage', 30);             // 'INSCRIPTION', 'RESET_PASSWORD'
            $table->string('code', 10);                   // code OTP à 6 chiffres
            $table->dateTime('expire_at');                // date d'expiration
            $table->integer('tentatives')->default(0);    // nombre de tentatives
            $table->boolean('utilise')->default(false);   // déjà utilisé ou pas
            $table->timestamps();

            // Index pour accélérer les recherches
            $table->index(['identifiant', 'type_usage']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('verification_codes');
    }
};