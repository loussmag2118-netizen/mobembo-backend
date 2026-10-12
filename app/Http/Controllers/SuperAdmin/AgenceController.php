<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Agence;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AgenceController extends Controller
{
    /**
     * Afficher la liste de TOUTES les agences et leurs informations globales.
     */
    public function index()
    {
        $agences = Agence::with(['admin'])
            ->withCount(['utilisateurs as agents_count' => function($query) {
                $query->where('role', 'Agent');
            }, 'bus'])
            ->orderBy('id', 'desc')
            ->get();

        $totalAgences = $agences->count();
        $agencesActives = $agences->where('statut', 'Actif')->count();
        $totalAgents = $agences->sum('agents_count');
        $totalBus = $agences->sum('bus_count');

        return view('superadmin.agences.index', compact(
            'agences',
            'totalAgences',
            'agencesActives',
            'totalAgents',
            'totalBus'
        ));
    }

    /**
     * Enregistrer une nouvelle agence ET créer automatiquement son compte Admin.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nom_agence'       => 'required|string|max:100|unique:agences,nom_agence',
            'date_partenariat' => 'required|date',
            'admin_telephone'  => 'required|string|regex:/^(05|06)\d{7}$/|unique:utilisateurs,telephone',
            'admin_nom'        => 'required|string|max:100',
            'admin_prenom'     => 'required|string|max:100',
            'admin_password'   => 'required|string|min:8',
        ], [
            'nom_agence.unique'        => 'Une agence avec ce nom existe déjà.',
            'admin_telephone.unique'   => 'Ce numéro de téléphone est déjà utilisé.',
            'admin_telephone.regex'    => 'Le numéro doit commencer par 05 ou 06 et avoir 9 chiffres.',
            'admin_password.min'       => 'Le mot de passe doit avoir au moins 8 caractères.',
        ]);

        // 1️⃣ Créer l'agence
        $agence = Agence::create([
            'nom_agence'       => $request->nom_agence,
            'statut'           => 'Actif',
            'date_partenariat' => $request->date_partenariat,
            'superadmin_id'    => Auth::id(),
        ]);

        // 2️⃣ Créer automatiquement le compte Admin de l'agence
        Utilisateur::create([
            'agence_id'        => $agence->id,
            'nom'              => $request->admin_nom,
            'prenom'           => $request->admin_prenom,
            'telephone'        => $request->admin_telephone,
            'role'             => 'Admin',
            'mot_de_passe'     => Hash::make($request->admin_password),
            'code_acces_agent' => null,
            'statut'           => 'Actif',
            'compte_confirme'  => true,
        ]);

        return redirect()
            ->route('superadmin.agences.index')
            ->with('success', "Agence « {$agence->nom_agence} » créée avec succès. Le compte Admin a été configuré.");
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
