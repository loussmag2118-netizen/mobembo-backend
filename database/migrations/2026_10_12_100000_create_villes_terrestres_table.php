<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Référentiel des villes du transport terrestre (géré par le Super Admin).
 * Distinct de la table `villes` qui sert au module Canadian (vols, codes IATA).
 *
 * Chaque gare est rattachée à une ville (gares.ville_id). La colonne texte
 * gares.ville est conservée et synchronisée, car tickets, API et écrans la lisent.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('villes_terrestres', function (Blueprint $table) {
            $table->id();
            $table->string('nom', 100)->unique();
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });

        Schema::table('gares', function (Blueprint $table) {
            $table->foreignId('ville_id')
                  ->nullable()
                  ->after('agence_id')
                  ->constrained('villes_terrestres')
                  ->restrictOnDelete();
        });

        // ── Reprise des données existantes ─────────────────────────────
        $villesParDefaut = ['Brazzaville', 'Pointe-Noire', 'Dolisie', 'Nkayi', 'Ouesso', 'Oyo', 'Owando'];

        // Seules les villes des gares comptent : celles des trajets (saisie libre,
        // parfois mal orthographiées) sont recalculées depuis les gares plus bas.
        $villesExistantes = DB::table('gares')->pluck('ville');

        // Dédoublonnage insensible à la casse : la première orthographe rencontrée l'emporte
        $villes = [];
        foreach (collect($villesParDefaut)->merge($villesExistantes) as $nom) {
            $nom = trim((string) $nom);
            if ($nom === '') continue;
            $villes[mb_strtolower($nom)] ??= $nom;
        }

        $now = now();
        foreach ($villes as $nom) {
            DB::table('villes_terrestres')->insert([
                'nom' => $nom, 'actif' => true, 'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        // Rattache chaque gare à sa ville et harmonise l'orthographe
        foreach (DB::table('villes_terrestres')->get() as $ville) {
            DB::table('gares')
                ->whereRaw('LOWER(TRIM(ville)) = ?', [mb_strtolower($ville->nom)])
                ->update(['ville_id' => $ville->id, 'ville' => $ville->nom]);
        }

        // Les villes des trajets sont celles de leurs gares
        DB::table('trajets')
            ->join('gares as gd', 'gd.id', '=', 'trajets.gare_depart_id')
            ->join('gares as ga', 'ga.id', '=', 'trajets.gare_arrivee_id')
            ->update([
                'trajets.ville_depart'  => DB::raw('gd.ville'),
                'trajets.ville_arrivee' => DB::raw('ga.ville'),
            ]);
    }

    public function down(): void
    {
        Schema::table('gares', function (Blueprint $table) {
            $table->dropConstrainedForeignId('ville_id');
        });

        Schema::dropIfExists('villes_terrestres');
    }
};
