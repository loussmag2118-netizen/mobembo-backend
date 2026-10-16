<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Agence;
use Illuminate\Http\Request;

class AgenceApiController extends Controller
{
    /**
     * Liste publique des agences actives pour l'application mobile.
     * GET /api/agences
     */
    public function index()
    {
        // Compagnies de bus seulement (les compagnies aériennes ont leur propre API)
        $agences = Agence::deBus()->with(['logoMedia', 'photos'])
            ->where('statut', 'Actif')
            ->orderBy('nom_agence')
            ->get()
            ->map(function ($a) {
                return [
                    'id'               => $a->id,
                    'nom'              => $a->nom_agence,
                    'nom_agence'       => $a->nom_agence,
                    'logo'             => $a->logo_app,
                    // Vitrine gérée par l'agence (logo, couleur, slogan, photos)
                    'vitrine'          => $a->vitrine(),
                    'date_partenariat' => $a->date_partenariat,
                    'statut'           => $a->statut,
                ];
            });

        return response()->json([
            'success' => true,
            'agences' => $agences,
        ], 200);
    }
}
