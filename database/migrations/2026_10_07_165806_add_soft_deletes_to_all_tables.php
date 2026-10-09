<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Toutes les tables qui doivent avoir une colonne deleted_at
     * (parce que leur modèle utilise SoftDeletes).
     */
    private array $tables = [
        'bus',
        'gares',
        'trajets',
        'vagues',
        'vagues_depart',
        'planification_voyages',
        'planifications',
        'tickets',
        'reservations',
        'vols',
        'promos',
        'flotte',
        'villes',
        'extras',
        'classes_tarifaires',
        'passagers',
        'notifications',
        'paiements',
        'carnet_passagers',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table) && !Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->softDeletes();
                });
                echo "✅ deleted_at ajouté à : {$table}\n";
            } else {
                echo "⏭️  ignoré : {$table}\n";
            }
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropSoftDeletes();
                });
            }
        }
    }
};