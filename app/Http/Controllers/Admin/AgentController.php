<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Utilisateur;
use App\Models\Gare;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AgentController extends Controller
{
    /**
     * Liste des agents actifs/inactifs de l'agence connectée.
     */
    public function index()
    {
        $agents = Utilisateur::where('agence_id', Auth::user()->agence_id)
            ->where('role', 'Agent')
            ->with('gare') // Charger la gare associée pour l'affichage
            ->get();

        return view('admin.agents.index', compact('agents'));
    }

    /**
     * Formulaire d'ajout d'un nouvel agent.
     */
    public function create()
    {
        // Récupérer uniquement les gares de l'agence connectée
        $gares = Gare::where('agence_id', Auth::user()->agence_id)->orderBy('nom_gare')->get();

        return view('admin.agents.create', compact('gares'));
    }

    /**
     * Enregistrer un nouvel agent rattaché d'office à l'agence et à une gare.
     */
    public function store(Request $request)
    {
        // Normalise le numéro avant validation : +24206... → 06...
        $request->merge([
            'telephone' => $this->normalizeTelephone($request->input('telephone')),
        ]);

        $request->validate([
            'nom'               => 'required|string|max:100',
            'prenom'            => 'required|string|max:100',
            'gare_id'           => 'required|exists:gares,id', // 🔒 Doit exister
            'telephone'         => [
                'required',
                'string',
                'regex:/^06\d{7}$/',
                'unique:utilisateurs,telephone',
            ],
            'code_acces_agent'  => 'nullable|string|max:50|unique:utilisateurs,code_acces_agent',
        ], [
            'telephone.regex'   => 'Le téléphone doit être au format 06XXXXXXX (9 chiffres).',
            'telephone.unique'  => 'Ce numéro de téléphone est déjà utilisé.',
            'code_acces_agent.unique' => 'La valeur du champ code acces agent est déjà utilisée.',
            'gare_id.required'  => 'Veuillez assigner une gare à cet agent.',
            'gare_id.exists'    => 'La gare sélectionnée est invalide.',
        ]);

        // Sécurité supplémentaire : vérifier que la gare appartient bien à l'agence de l'admin
        $gare = Gare::where('id', $request->gare_id)
            ->where('agence_id', Auth::user()->agence_id)
            ->firstOrFail();

        // Générer un code d'accès unique si non fourni ou si le champ est vide
        $codeAcces = $request->code_acces_agent;
        if (empty($codeAcces)) {
            do {
                $codeAcces = 'AG' . rand(1000, 9999);
            } while (Utilisateur::where('code_acces_agent', $codeAcces)->exists());
        }

        Utilisateur::create([
            'agence_id'         => Auth::user()->agence_id,
            'gare_id'           => $gare->id, // ✅ Enregistre bien la gare
            'nom'               => $request->nom,
            'prenom'            => $request->prenom,
            'telephone'         => $request->telephone,
            'role'              => 'Agent',
            'mot_de_passe'      => Hash::make('Mobembo2026'),
            'code_acces_agent'  => $codeAcces,
            'statut'            => 'actif',
            'compte_confirme'   => true,
        ]);

        return redirect()
            ->route('admin.agents.index')
            ->with('success', 'Agent créé, rattaché à sa gare et à votre agence avec succès.');
    }

    /**
     * Formulaire de modification d'un agent.
     */
    public function edit(Utilisateur $agent)
    {
        $this->authorizeAgence($agent);

        // Récupérer les gares de l'agence pour le sélecteur
        $gares = Gare::where('agence_id', Auth::user()->agence_id)->orderBy('nom_gare')->get();

        return view('admin.agents.edit', compact('agent', 'gares'));
    }

    /**
     * Mettre à jour les informations d'un agent.
     */
    public function update(Request $request, Utilisateur $agent)
    {
        $this->authorizeAgence($agent);

        // Normalise le numéro avant validation : +24206... → 06...
        $request->merge([
            'telephone' => $this->normalizeTelephone($request->input('telephone')),
        ]);

        $request->validate([
            'nom'               => 'required|string|max:100',
            'prenom'            => 'required|string|max:100',
            'gare_id'           => 'required|exists:gares,id',
            'telephone'         => [
                'required',
                'string',
                'regex:/^06\d{7}$/',
                'unique:utilisateurs,telephone,' . $agent->id,
            ],
            'code_acces_agent'  => 'nullable|string|max:50|unique:utilisateurs,code_acces_agent,' . $agent->id,
            'statut'            => 'required|in:actif,suspendu',
        ], [
            'telephone.regex'   => 'Le téléphone doit être au format 06XXXXXXX (9 chiffres).',
            'telephone.unique'  => 'Ce numéro de téléphone est déjà utilisé.',
            'code_acces_agent.unique' => 'La valeur du champ code acces agent est déjà utilisée.',
            'gare_id.required'  => 'Veuillez assigner une gare à cet agent.',
        ]);

        // Vérifier que la nouvelle gare appartient bien à l'agence
        Gare::where('id', $request->gare_id)
            ->where('agence_id', Auth::user()->agence_id)
            ->firstOrFail();

        $agent->update([
            'nom'               => $request->nom,
            'prenom'            => $request->prenom,
            'gare_id'           => $request->gare_id, // ✅ Met à jour bien la gare
            'telephone'         => $request->telephone,
            'code_acces_agent'  => $request->code_acces_agent,
            'statut'            => $request->statut,
        ]);

        return redirect()
            ->route('admin.agents.index')
            ->with('success', 'Agent mis à jour avec succès.');
    }

    /**
     * Envoyer un agent dans la corbeille (Soft Delete).
     */
    public function destroy(Utilisateur $agent)
    {
        $this->authorizeAgence($agent);

        $agent->delete();

        return redirect()
            ->route('admin.agents.index')
            ->with('success', 'Agent envoyé dans la corbeille avec succès.');
    }

    /**
     * Afficher la liste des agents supprimés (Corbeille) pour l'agence connectée.
     */
    public function trashed()
    {
        $agents = Utilisateur::onlyTrashed()
            ->where('agence_id', Auth::user()->agence_id)
            ->where('role', 'Agent')
            ->with('gare')
            ->get();

        return view('admin.agents.trashed', compact('agents'));
    }

    /**
     * Restaurer un agent depuis la corbeille.
     */
    public function restore($id)
    {
        $agent = Utilisateur::onlyTrashed()->findOrFail($id);

        $this->authorizeAgence($agent);

        $agent->restore();

        return redirect()
            ->route('admin.agents.trashed')
            ->with('success', 'Agent restauré avec succès.');
    }

    /**
     * Supprimer définitivement un agent de la base de données.
     */
    public function forceDelete($id)
    {
        $agent = Utilisateur::onlyTrashed()->findOrFail($id);

        $this->authorizeAgence($agent);

        $agent->forceDelete();

        return redirect()
            ->route('admin.agents.trashed')
            ->with('success', 'Agent supprimé définitivement.');
    }

    /**
     * Afficher l'historique des opérations/tickets de l'agent.
     */
    public function historique(Utilisateur $agent)
    {
        $this->authorizeAgence($agent);

        $operations = Ticket::where('agence_id', Auth::user()->agence_id)
            ->where(function ($query) use ($agent) {
                $query->where('agent_vendeur_id', $agent->id)
                      ->orWhere('agent_controleur_id', $agent->id);
            })
            ->latest()
            ->paginate(10);

        return view('admin.agents.historique', compact('agent', 'operations'));
    }

    /**
     * Normalise un numéro de téléphone au format 06XXXXXXX (9 chiffres).
     */
    private function normalizeTelephone(?string $telephone): ?string
    {
        if (!$telephone) {
            return null;
        }

        $tel = preg_replace('/[\s\-\.\(\)]/', '', $telephone);

        if (str_starts_with($tel, '00242')) {
            $tel = substr($tel, 5);
        } elseif (str_starts_with($tel, '+242')) {
            $tel = substr($tel, 4);
        } elseif (str_starts_with($tel, '242')) {
            $tel = substr($tel, 3);
        }

        if (!str_starts_with($tel, '0')) {
            $tel = '0' . $tel;
        }

        return $tel;
    }

    /**
     * Vérification de sécurité absolue : s'assure que l'agent appartient bien
     * à l'agence de l'administrateur connecté.
     */
    private function authorizeAgence(Utilisateur $agent): void
    {
        abort_if(
            $agent->agence_id !== Auth::user()->agence_id ||
            $agent->role !== 'Agent',
            403,
            'Accès non autorisé à cet agent.'
        );
    }
}
