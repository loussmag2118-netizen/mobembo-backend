<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Agence;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AgenceController extends Controller
{
    /**
     * Afficher la liste de TOUTES les agences et leurs informations globales.
     */
    public function index()
    {
        // On récupère les agences avec le nombre d'agents et de bus associés pour avoir "toutes les informations"
        $agences = Agence::withCount(['utilisateurs as agents_count' => function($query) {
            $query->where('role', 'Agent');
        }, 'bus'])->get();

        return view('superadmin.agences.index', compact('agences'));
    }

    /**
     * Enregistrer une nouvelle agence (Point 7).
     */
    public function store(Request $request)
    {
        $request->validate([
            'nom_agence' => 'required|string|max:100|unique:agences,nom_agence',
            'date_partenariat' => 'required|date',
        ]);

        Agence::create([
            'nom_agence' => $request->nom_agence,
            'statut' => 'Actif', // Actif par défaut à la création
            'date_partenariat' => $request->date_partenariat,
            'superadmin_id' => Auth::id(), // ID du Super Admin connecté
        ]);

        return redirect()
            ->route('superadmin.agences.index')
            ->with('success', 'Nouvelle agence créée avec succès sur la plateforme.');
    }

    /**
     * Changer le statut d'une agence (Actif / Bloqué) pour le contrôle global.
     */
    public function toggleStatus(Agence $agence)
    {
        $agence->statut = $agence->statut === 'Actif' ? 'Bloqué' : 'Actif';
        $agence->save();

        return redirect()
            ->route('superadmin.agences.index')
            ->with('success', "Le statut de l'agence {$agence->nom_agence} a été mis à jour.");
    }
}
