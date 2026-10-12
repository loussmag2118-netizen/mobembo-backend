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
        $agences = Agence::where('statut', 'Actif')
            ->orderBy('nom_agence')
            ->get()
            ->map(function ($a) {
                // Cartographie des logos vers les assets mobiles connus
                $logos = [
                    'Océan du Nord'   => 'assets/images/oceandunord.png',
                    'Nzoko Transport' => 'assets/images/Nzoko.jpg',
                    'Stelimac'        => 'assets/images/Stelimac.jpg',
                    'Transbony'       => 'assets/images/transbony.png',
                ];

                return [
                    'id'               => $a->id,
                    'nom'              => $a->nom_agence,
                    'nom_agence'       => $a->nom_agence,
                    'logo'             => $logos[$a->nom_agence] ?? 'assets/images/Logo.png',
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
