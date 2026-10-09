<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Correspondance entre les noms d'agences et les codes couleurs hexadécimaux.
     */
    private array $couleursParAgence = [
        'Océan du Nord' => '#1B5E20', // Vert foncé
        'Transbony'     => '#0D47A1', // Bleu profond
        'Stelimac'      => '#E64A19', // Orange brique
        'Nzoko Transport' => '#F9A825', // Jaune/Ambre
    ];

    /**
     * Affiche le tableau de bord avec le thème personnalisé selon l'agence connectée.
     */
    public function index()
    {
        // On récupère l'administrateur connecté avec sa relation 'agence'
        $admin = Auth::user();
        
        // Sécurité : charger la relation si elle n'est pas déjà chargée
        if ($admin && !$admin->relationLoaded('agence')) {
            $admin->load('agence');
        }

        $nomAgence = $admin->agence->nom_agence ?? 'Standard';
        
        // Sélection de la couleur, défaut à Bleu marine (#1e3a8a) si non trouvée
        $couleur = $this->couleursParAgence[$nomAgence] ?? '#1e3a8a';

        return view('admin.dashboard', [
            'admin' => $admin,
            'couleurTheme' => $couleur,
            'nomAgence' => $nomAgence,
        ]);
    }
}
