<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Complète la table des comptes Canadian Airways : colonnes attendues par
 * l'inscription mobile (nom, prénom, confirmation) et rôle administrateur.
 * Seuls les comptes est_admin peuvent entrer dans l'administration web ;
 * les clients de l'application n'ont accès qu'à l'API mobile.
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
            if (! Schema::hasColumn('canadian_users', 'est_admin')) {
                $table->boolean('est_admin')->default(false)->index()->after('compte_confirme');
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
            $table->dropIndex(['est_admin']);
            $table->dropColumn(['nom', 'prenom', 'compte_confirme', 'est_admin', 'email_verified_at', 'remember_token']);
        });
    }
};
