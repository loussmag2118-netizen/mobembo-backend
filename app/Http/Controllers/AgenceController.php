<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Agence;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AgenceController extends Controller
{
    /**
     * Liste toutes les agences avec compteurs.
     */
    public function index()
    {
        $agences = Agence::withCount(['agents', 'bus'])->get();

        return view('superadmin.agences.index', compact('agences'));
    }

    /**
     * Crée une nouvelle agence.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom_agence'       => ['required', 'string', 'max:255'],
            'date_partenariat' => ['required', 'date'],
        ]);

        Agence::create([
            'nom_agence'       => $validated['nom_agence'],
            'date_partenariat' => $validated['date_partenariat'],
            'statut'           => 'Actif',
        ]);

        return redirect()->back()->with('success', 'Agence créée avec succès.');
    }

    /**
     * Bloque ou débloque une agence.
     * Si bloquée : déconnecte immédiatement tous les utilisateurs de l'agence.
     */
    public function toggleStatus($id)
    {
        $agence = Agence::findOrFail($id);

        $agence->statut = $agence->statut === 'Actif' ? 'Bloqué' : 'Actif';
        $agence->save();

        if ($agence->statut === 'Bloqué') {
            // Invalide les sessions actives des utilisateurs de cette agence
            // (fonctionne si SESSION_DRIVER=database dans .env)
            try {
                $userIds = Utilisateur::where('agence_id', $agence->id)->pluck('id');
                DB::table('sessions')->whereIn('user_id', $userIds)->delete();
            } catch (\Throwable $e) {
                // Si le driver session n'est pas "database", on ignore silencieusement.
                // Le middleware CheckAgenceActive les déconnectera à leur prochaine requête.
            }

            return redirect()->back()->with('success',
                'Agence « ' . $agence->nom_agence . ' » bloquée. Son administrateur et ses agents ne peuvent plus se connecter.'
            );
        }

        return redirect()->back()->with('success',
            'Agence « ' . $agence->nom_agence . ' » réactivée avec succès.'
        );
    }
}