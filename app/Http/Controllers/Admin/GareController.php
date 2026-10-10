<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Gare;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Throwable;

class GareController extends Controller
{
    /**
     * Affiche la liste des gares appartenant à l'agence de l'administrateur connecté.
     */
    public function index()
    {
        $gares = Gare::where('agence_id', Auth::user()->agence_id)->get();
        return view('admin.gares.index', compact('gares'));
    }

    /**
     * Affiche le formulaire de création d'une nouvelle gare routière.
     */
    public function create()
    {
        return view('admin.gares.create');
    }

    /**
     * Enregistre une nouvelle gare routière liée à l'agence connectée.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nom_gare' => 'required|string|max:100',
            'ville' => 'required|string|max:100', // <--- Modifié pour accepter toute saisie libre de l'admin
        ]);

        Gare::create([
            'agence_id' => Auth::user()->agence_id,
            'nom_gare' => $request->nom_gare,
            'ville' => $request->ville,
        ]);

        return redirect()->route('admin.gares.index')->with('success', 'Gare ajoutée avec succès.');
    }

    /**
     * Affiche le formulaire d'édition d'une gare existante.
     */
    public function edit(Gare $gare)
    {
        $this->authorizeAgence($gare);
        return view('admin.gares.edit', compact('gare'));
    }

    /**
     * Met à jour les informations d'une gare routière.
     */
    public function update(Request $request, Gare $gare)
    {
        $this->authorizeAgence($gare);

        $request->validate([
            'nom_gare' => 'required|string|max:100',
            'ville' => 'required|string|max:100', // <--- Modifié pour accepter toute saisie libre de l'admin
        ]);

        $gare->update($request->only('nom_gare', 'ville'));

        return redirect()->route('admin.gares.index')->with('success', 'Gare mise à jour avec succès.');
    }

    /**
     * Supprime une gare routière de la base de données.
     */
    public function destroy(Gare $gare)
    {
        $this->authorizeAgence($gare);
        $gare->delete();
        
        return redirect()->route('admin.gares.index')->with('success', 'Gare supprimée avec succès.');
    }

    /**
     * ⭐ API Mobile : Retourne la liste des gares en JSON.
     * GET /api/gares
     */
    public function apiIndex(Request $request)
    {
        try {
            $agenceNom = $request->query('agence');

            $query = Gare::orderBy('nom_gare');

            if ($agenceNom) {
                $query->whereHas('agence', function ($q) use ($agenceNom) {
                    $q->where('nom_agence', 'LIKE', "%{$agenceNom}%");
                });
            }

            $gares = $query->get()->map(fn($g) => [
                'id'       => $g->id,
                'nom_gare' => $g->nom_gare,
                'ville'    => $g->ville,
                'agence_id'=> $g->agence_id,
            ])->values();

            return response()->json($gares, 200);
        } catch (Throwable $e) {
            return response()->json(['message' => 'Erreur : ' . $e->getMessage()], 500);
        }
    }

    /**
     * Vérification de sécurité : s'assure que la gare appartient bien à l'agence de l'administrateur connecté.
     */
    private function authorizeAgence(Gare $gare): void
    {
        abort_if($gare->agence_id !== Auth::user()->agence_id, 403, 'Accès non autorisé à cette gare.');
    }
}
