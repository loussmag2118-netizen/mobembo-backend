<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('notifications')) {
            return;
        }

        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('canadian_user_id')
                  ->nullable()
                  ->constrained('canadian_users')
                  ->nullOnDelete();
            $table->foreignId('utilisateur_id')
                  ->nullable()
                  ->constrained('utilisateurs')
                  ->nullOnDelete();

            $table->string('titre');
            $table->text('message');
            $table->string('type', 50)->default('info');
            $table->boolean('lu')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};