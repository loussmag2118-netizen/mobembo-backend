<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Gare;
use App\Models\Trajet;
use App\Models\VilleTerrestre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Throwable;

class GareController extends Controller
{
    /**
     * Affiche la liste des gares appartenant à l'agence de l'administrateur connecté.
     */
    public function index()
    {
        $gares = Gare::where('agence_id', Auth::user()->agence_id)
            ->orderBy('ville')
            ->orderBy('nom_gare')
            ->paginate(5)->withQueryString();
        return view('admin.gares.index', compact('gares'));
    }

    /**
     * Affiche le formulaire de création d'une nouvelle gare routière.
     */
    public function create()
    {
        $villes = $this->villesProposees();
        return view('admin.gares.create', compact('villes'));
    }

    /**
     * Enregistre une nouvelle gare routière liée à l'agence connectée.
     */
    public function store(Request $request)
    {
        $ville = $this->validerVille($request);

        Gare::create([
            'agence_id' => Auth::user()->agence_id,
            'ville_id'  => $ville->id,
            'nom_gare'  => trim($request->nom_gare),
            'ville'     => $ville->nom,
        ]);

        return redirect()->route('admin.gares.index')->with('success', 'Gare ajoutée avec succès.');
    }

    /**
     * Affiche le formulaire d'édition d'une gare existante.
     */
    public function edit(Gare $gare)
    {
        $this->authorizeAgence($gare);
        $villes = $this->villesProposees($gare);
        return view('admin.gares.edit', compact('gare', 'villes'));
    }

    /**
     * Met à jour les informations d'une gare routière.
     */
    public function update(Request $request, Gare $gare)
    {
        $this->authorizeAgence($gare);

        $ville = $this->validerVille($request, $gare);

        $gare->update([
            'ville_id' => $ville->id,
            'nom_gare' => trim($request->nom_gare),
            'ville'    => $ville->nom,
        ]);

        // Les trajets reprennent la ville de leurs gares
        Trajet::where('gare_depart_id', $gare->id)->update(['ville_depart' => $ville->nom]);
        Trajet::where('gare_arrivee_id', $gare->id)->update(['ville_arrivee' => $ville->nom]);

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
                'ville_id' => $g->ville_id,
                'ville'    => $g->ville,
                'agence_id'=> $g->agence_id,
            ])->values();

            return response()->json($gares, 200);
        } catch (Throwable $e) {
            return response()->json(['message' => 'Erreur : ' . $e->getMessage()], 500);
        }
    }

    /**
     * Villes proposées dans le formulaire : les villes actives du référentiel
     * (géré par le Super Admin), plus la ville actuelle de la gare si elle a été désactivée.
     */
    private function villesProposees(?Gare $gare = null)
    {
        return VilleTerrestre::where('actif', true)
            ->when($gare?->ville_id, fn ($q, $id) => $q->orWhere('id', $id))
            ->orderBy('nom')
            ->get();
    }

    /**
     * Valide le formulaire et retourne la ville choisie.
     */
    private function validerVille(Request $request, ?Gare $gare = null): VilleTerrestre
    {
        $request->validate([
            'nom_gare' => 'required|string|max:100',
            'ville_id' => 'required|integer',
        ], [
            'ville_id.required' => 'Veuillez choisir la ville de l\'agence.',
        ]);

        $ville = $this->villesProposees($gare)->firstWhere('id', (int) $request->ville_id);

        if (!$ville) {
            throw ValidationException::withMessages([
                'ville_id' => 'Cette ville n\'est pas disponible. Contactez le Super Admin pour l\'ajouter.',
            ]);
        }

        $doublon = Gare::where('agence_id', Auth::user()->agence_id)
            ->where('nom_gare', trim($request->nom_gare))
            ->where('ville', $ville->nom)
            ->when($gare, fn ($q) => $q->where('id', '!=', $gare->id))
            ->exists();

        if ($doublon) {
            throw ValidationException::withMessages([
                'nom_gare' => "Vous avez déjà une agence portant ce nom à {$ville->nom}.",
            ]);
        }

        return $ville;
    }

    /**
     * Vérification de sécurité : s'assure que la gare appartient bien à l'agence de l'administrateur connecté.
     */
    private function authorizeAgence(Gare $gare): void
    {
        abort_if($gare->agence_id !== Auth::user()->agence_id, 403, 'Accès non autorisé à cette agence.');
    }
}
