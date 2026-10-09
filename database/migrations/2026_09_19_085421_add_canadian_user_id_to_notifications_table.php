<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            if (!Schema::hasColumn('notifications', 'canadian_user_id')) {
                $table->foreignId('canadian_user_id')
                      ->nullable()
                      ->after('id')
                      ->constrained('canadian_users')
                      ->nullOnDelete();
            }

            if (!Schema::hasColumn('notifications', 'utilisateur_id')) {
                $table->foreignId('utilisateur_id')
                      ->nullable()
                      ->after('canadian_user_id')
                      ->constrained('utilisateurs')
                      ->nullOnDelete();
            }

            if (!Schema::hasColumn('notifications', 'type')) {
                $table->string('type', 50)->default('info')->after('message');
            }

            if (!Schema::hasColumn('notifications', 'lu')) {
                $table->boolean('lu')->default(false)->after('type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            if (Schema::hasColumn('notifications', 'canadian_user_id')) {
                $table->dropForeign(['canadian_user_id']);
                $table->dropColumn('canadian_user_id');
            }
            if (Schema::hasColumn('notifications', 'utilisateur_id')) {
                $table->dropForeign(['utilisateur_id']);
                $table->dropColumn('utilisateur_id');
            }
            if (Schema::hasColumn('notifications', 'type')) {
                $table->dropColumn('type');
            }
            if (Schema::hasColumn('notifications', 'lu')) {
                $table->dropColumn('lu');
            }
        });
    }
};