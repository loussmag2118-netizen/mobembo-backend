<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Gare;
use Illuminate\Http\Request;

class DataController extends Controller
{
    /**
     * Récupère la liste des villes et des gares associées pour Flutter.
     */
    public function getGaresParVille()
    {
        // Récupère toutes les gares de la base de données
        $gares = Gare::all();

        // Groupe les gares par ville pour structurer la réponse (ex: 'Brazzaville' => ['Gare A', 'Gare B'])
        $agencesParVille = $gares->groupBy('ville')->map(function ($items) {
            return $items->pluck('nom_gare')->unique()->values();
        });

        return response()->json([
            'success' => true,
            'villes' => $agencesParVille->keys(),
            'agencesParVille' => $agencesParVille,
        ]);
    }
}
