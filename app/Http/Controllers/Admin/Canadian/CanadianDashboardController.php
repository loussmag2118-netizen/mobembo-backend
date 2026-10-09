<?php

namespace App\Http\Controllers\Admin\Canadian;

use App\Http\Controllers\Controller;
use App\Models\Utilisateur;
use Illuminate\Support\Facades\Auth;

class CanadianDashboardController extends Controller
{
    /**
     * Afficher le tableau de bord administrateur de Canadian Airways.
     */
    public function index()
    {
        // Utilisateur connecté (guard canadian)
        $user = Auth::guard('canadian')->user();

        // Statistiques
        $totalClients      = Utilisateur::where('role', 'Client')->count();
        $totalUtilisateurs = Utilisateur::count();
        $totalAgents       = Utilisateur::where('role', 'Agent')->count();
        $totalAdmins       = Utilisateur::where('role', 'Admin')->count();

        return view('admin.canadian.dashboard', compact(
            'user',
            'totalClients',
            'totalUtilisateurs',
            'totalAgents',
            'totalAdmins'
        ));
    }
} 