<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\VilleTerrestre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Référentiel des villes du transport terrestre.
 * Seul le Super Admin crée, renomme, active ou supprime une ville ;
 * les admins d'agence choisissent ensuite la ville de leurs gares dans cette liste.
 */
class VilleController extends Controller
{
    public function index()
    {
        $villes = VilleTerrestre::withCount('gares')
            ->withCount(['gares as agences_count' => function ($q) {
                $q->select(DB::raw('COUNT(DISTINCT agence_id)'));
            }])
            ->orderBy('nom')
            ->paginate(5)->withQueryString();

        // Chiffres de toutes les villes (pas seulement de la page)
        $stats = [
            'villes'  => VilleTerrestre::count(),
            'actives' => VilleTerrestre::where('actif', true)->count(),
            'gares'   => \App\Models\Gare::whereIn('ville_id', VilleTerrestre::select('id'))->count(),
        ];

        return view('superadmin.villes.index', compact('villes', 'stats'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nom' => 'required|string|max:100|unique:villes_terrestres,nom',
        ], [
            'nom.unique' => 'Cette ville existe déjà.',
        ]);

        VilleTerrestre::create(['nom' => $this->formaterNom($data['nom']), 'actif' => true]);

        return redirect()->route('superadmin.villes.index')->with('success', 'Ville ajoutée avec succès.');
    }

    public function update(Request $request, VilleTerrestre $ville)
    {
        $data = $request->validate([
            'nom' => ['required', 'string', 'max:100', Rule::unique('villes_terrestres', 'nom')->ignore($ville->id)],
        ], [
            'nom.unique' => 'Une autre ville porte déjà ce nom.',
        ]);

        $ville->update(['nom' => $this->formaterNom($data['nom'])]);
        $ville->synchroniserNom();

        return redirect()->route('superadmin.villes.index')
            ->with('success', 'Ville renommée. Les gares et trajets concernés ont été mis à jour.');
    }

    /**
     * Une ville désactivée n'est plus proposée pour les nouvelles gares ;
     * les gares existantes continuent de fonctionner.
     */
    public function toggle(VilleTerrestre $ville)
    {
        $ville->update(['actif' => !$ville->actif]);

        return redirect()->route('superadmin.villes.index')
            ->with('success', $ville->actif ? 'Ville réactivée.' : 'Ville désactivée : elle n\'est plus proposée aux agences.');
    }

    public function destroy(VilleTerrestre $ville)
    {
        if ($ville->gares()->exists()) {
            return redirect()->route('superadmin.villes.index')
                ->with('error', "Impossible de supprimer {$ville->nom} : des gares y sont rattachées. Désactivez-la plutôt.");
        }

        $ville->delete();

        return redirect()->route('superadmin.villes.index')->with('success', 'Ville supprimée.');
    }

    /**
     * "  pointe-Noire " → "Pointe-Noire" (espaces nettoyés, première lettre en majuscule)
     */
    private function formaterNom(string $nom): string
    {
        $nom = trim(preg_replace('/\s+/', ' ', $nom));
        return mb_strtoupper(mb_substr($nom, 0, 1)) . mb_substr($nom, 1);
    }
}
