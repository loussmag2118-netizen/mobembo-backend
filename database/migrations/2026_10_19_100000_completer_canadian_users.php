<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Complète la table des clients de l'application aérienne : colonnes
 * attendues par l'inscription mobile (nom, prénom, confirmation).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('canadian_users', function (Blueprint $table) {
            if (! Schema::hasColumn('canadian_users', 'nom')) {
                $table->string('nom')->nullable()->after('name');
            }
            if (! Schema::hasColumn('canadian_users', 'prenom')) {
                $table->string('prenom')->nullable()->after('nom');
            }
            if (! Schema::hasColumn('canadian_users', 'compte_confirme')) {
                $table->boolean('compte_confirme')->default(false)->after('password');
            }
            if (! Schema::hasColumn('canadian_users', 'email_verified_at')) {
                $table->timestamp('email_verified_at')->nullable()->after('email');
            }
            if (! Schema::hasColumn('canadian_users', 'remember_token')) {
                $table->rememberToken();
            }
        });
    }

    public function down(): void
    {
        Schema::table('canadian_users', function (Blueprint $table) {
            $table->dropColumn(['nom', 'prenom', 'compte_confirme', 'email_verified_at', 'remember_token']);
        });
    }
};
