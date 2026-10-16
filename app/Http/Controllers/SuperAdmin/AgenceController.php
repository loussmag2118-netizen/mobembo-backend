<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Agence;
use App\Models\Utilisateur;
use App\Support\Audit;
use App\Support\Couleurs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AgenceController extends Controller
{
    /**
     * Afficher la liste de TOUTES les agences et leurs informations globales.
     */
    public function index(Request $request)
    {
        $type = in_array($request->query('type'), [Agence::BUS, Agence::AERIEN], true) ? $request->query('type') : null;

        $agences = Agence::with(['admin', 'logoMedia'])
            ->withCount(['utilisateurs as agents_count' => function($query) {
                $query->where('role', 'Agent');
            }, 'bus', 'vols'])
            ->when($type, fn ($q) => $q->where('type_transport', $type))
            ->orderBy('id', 'desc')
            ->paginate(5)->withQueryString();

        // Chiffres de toute la plateforme (pas seulement de la page)
        $totalAgences = Agence::count();
        $agencesActives = Agence::where('statut', 'Actif')->count();
        $totalAgents = Utilisateur::where('role', 'Agent')->whereNotNull('agence_id')->count();
        $totalBus = \App\Models\Bus::count();

        return view('superadmin.agences.index', compact(
            'agences',
            'totalAgences',
            'agencesActives',
            'totalAgents',
            'totalBus',
            'type'
        ));
    }

    /**
     * Enregistrer une nouvelle agence ET créer son compte Admin, ensemble :
     * si la création de l'admin échoue, l'agence n'est pas créée non plus.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nom_agence'       => 'required|string|max:100|unique:agences,nom_agence',
            'type_transport'   => 'nullable|in:bus,aerien',
            'date_partenariat' => 'required|date',
            // Liste de règles : le « | » de l'expression ne doit pas séparer les règles
            'admin_telephone'  => ['required', 'string', 'regex:/^(05|06)\d{7}$/', 'unique:utilisateurs,telephone'],
            'admin_nom'        => 'required|string|max:100',
            'admin_prenom'     => 'required|string|max:100',
            'admin_password'   => 'required|string|min:8',
            // Identité visuelle (facultative, modifiable ensuite par l'agence)
            'couleur'            => ['nullable', Couleurs::REGLE],
            'couleur_secondaire' => ['nullable', Couleurs::REGLE],
            'logo'               => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'couleur.regex'            => 'La couleur doit être au format #RRGGBB.',
            'couleur_secondaire.regex' => 'La couleur doit être au format #RRGGBB.',
            'logo.max'                 => 'Le logo ne doit pas dépasser 2 Mo.',
            'nom_agence.unique'        => 'Un partenaire avec ce nom existe déjà.',
            'admin_telephone.unique'   => 'Ce numéro de téléphone est déjà utilisé.',
            'admin_telephone.regex'    => 'Le numéro doit commencer par 05 ou 06 et avoir 9 chiffres.',
            'admin_password.min'       => 'Le mot de passe doit avoir au moins 8 caractères.',
        ]);

        $agence = DB::transaction(function () use ($request) {
            $agence = Agence::create([
                'nom_agence'       => $request->nom_agence,
                'type_transport'   => $request->input('type_transport', Agence::BUS),
                'statut'           => 'Actif',
                'date_partenariat' => $request->date_partenariat,
                'superadmin_id'    => Auth::id(),
                'couleur'            => $request->filled('couleur') ? strtoupper($request->couleur) : null,
                'couleur_secondaire' => $request->filled('couleur_secondaire') ? strtoupper($request->couleur_secondaire) : null,
            ]);

            if ($request->hasFile('logo')) {
                $agence->remplacerLogo($request->file('logo'));
            }

            $this->creerAdmin($agence, $request->admin_nom, $request->admin_prenom, $request->admin_telephone, $request->admin_password);

            return $agence;
        });

        Audit::log('superadmin.agence.creee', $agence, ['nom' => $agence->nom_agence, 'type' => $agence->type_transport, 'admin' => $request->admin_telephone]);

        return redirect()
            ->route('superadmin.agences.index')
            ->with('success', ($agence->estAerien() ? 'Compagnie aérienne' : 'Agence') . " « {$agence->nom_agence} » créée avec succès. Le compte Admin a été configuré.");
    }

    /**
     * Fiche d'une agence : réglages et comptes administrateurs.
     */
    public function show(Agence $agence)
    {
        $agence->loadCount([
            'utilisateurs as agents_count' => fn ($q) => $q->where('role', 'Agent'),
            'bus', 'gares', 'trajets', 'vols', 'flottes',
            'reservationsVols as reservations_payees_count' => fn ($q) => $q->whereIn('statut', ['Paye', 'Confirmee']),
        ]);
        $admins = Utilisateur::where('agence_id', $agence->id)->where('role', 'Admin')->orderBy('nom')->get();

        return view('superadmin.agences.show', compact('agence', 'admins'));
    }

    /**
     * Modifier les réglages d'une agence.
     */
    public function update(Request $request, Agence $agence)
    {
        $donnees = $request->validate([
            'nom_agence'       => ['required', 'string', 'max:100', Rule::unique('agences', 'nom_agence')->ignore($agence->id)],
            'date_partenariat' => 'required|date',
            // Réglages propres au bus
            'age_max_enfant'   => [$agence->estAerien() ? 'nullable' : 'required', 'integer', 'min:1', 'max:17'],
        ]);
        if ($agence->estAerien()) {
            unset($donnees['age_max_enfant']);
        } else {
            $donnees['choix_siege'] = $request->boolean('choix_siege');
        }

        $avant = $agence->only(array_keys($donnees));
        $agence->update($donnees);
        Audit::log('superadmin.agence.modifiee', $agence, ['avant' => $avant, 'apres' => $agence->only(array_keys($donnees))]);

        return back()->with('success', "Les réglages de « {$agence->nom_agence} » ont été enregistrés.");
    }

    /**
     * Couleurs et logo d'une agence (aussi modifiables par l'agence
     * depuis sa vitrine).
     */
    public function identite(Request $request, Agence $agence)
    {
        $donnees = $request->validate([
            'couleur'            => ['nullable', Couleurs::REGLE],
            'couleur_secondaire' => ['nullable', Couleurs::REGLE],
            'logo'               => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'couleur.regex'            => 'La couleur doit être au format #RRGGBB.',
            'couleur_secondaire.regex' => 'La couleur doit être au format #RRGGBB.',
            'logo.max'                 => 'Le logo ne doit pas dépasser 2 Mo.',
        ]);

        $agence->update([
            'couleur'            => isset($donnees['couleur']) ? strtoupper($donnees['couleur']) : null,
            'couleur_secondaire' => isset($donnees['couleur_secondaire']) ? strtoupper($donnees['couleur_secondaire']) : null,
        ]);
        if ($request->hasFile('logo')) {
            $agence->remplacerLogo($request->file('logo'));
        }
        Audit::log('superadmin.agence.identite', $agence, $agence->only(['couleur', 'couleur_secondaire', 'logo_media_id']));

        return back()->with('success', "L'identité visuelle de « {$agence->nom_agence} » a été enregistrée.");
    }

    /**
     * Ajouter un administrateur à une agence (mot de passe temporaire).
     */
    public function ajouterAdmin(Request $request, Agence $agence)
    {
        $request->validate([
            'nom'       => 'required|string|max:100',
            'prenom'    => 'required|string|max:100',
            'telephone' => ['required', 'string', 'regex:/^(05|06)\d{7}$/', 'unique:utilisateurs,telephone'],
        ], [
            'telephone.unique' => 'Ce numéro de téléphone est déjà utilisé.',
            'telephone.regex'  => 'Le numéro doit commencer par 05 ou 06 et avoir 9 chiffres.',
        ]);

        $motDePasse = self::motDePasseTemporaire();
        $admin = $this->creerAdmin($agence, $request->nom, $request->prenom, $request->telephone, $motDePasse);
        Audit::log('superadmin.admin.ajoute', $admin, ['telephone' => $admin->telephone], $agence->id);

        return back()
            ->with('success', "Administrateur {$admin->prenom} {$admin->nom} ajouté.")
            ->with('mot_de_passe_temporaire', ['compte' => "{$admin->prenom} {$admin->nom} ({$admin->telephone})", 'mot_de_passe' => $motDePasse]);
    }

    /**
     * Réinitialiser le mot de passe d'un administrateur d'agence.
     */
    public function reinitialiserAdmin(Agence $agence, Utilisateur $admin)
    {
        abort_unless((int) $admin->agence_id === (int) $agence->id && $admin->role === 'Admin', 404);

        $motDePasse = self::motDePasseTemporaire();
        $admin->update(['mot_de_passe' => Hash::make($motDePasse)]);
        Audit::log('superadmin.admin.mot_de_passe_reinitialise', $admin, ['telephone' => $admin->telephone], $agence->id);

        return back()->with('mot_de_passe_temporaire', [
            'compte' => "{$admin->prenom} {$admin->nom} ({$admin->telephone})",
            'mot_de_passe' => $motDePasse,
        ]);
    }

    /**
     * Activer / suspendre un administrateur d'agence.
     */
    public function basculerAdmin(Agence $agence, Utilisateur $admin)
    {
        abort_unless((int) $admin->agence_id === (int) $agence->id && $admin->role === 'Admin', 404);

        $admin->update(['statut' => $admin->statut === 'actif' ? 'suspendu' : 'actif']);
        Audit::log('superadmin.admin.statut', $admin, ['statut' => $admin->statut], $agence->id);

        return back()->with('success', "Le compte de {$admin->prenom} {$admin->nom} est maintenant " . ($admin->statut === 'actif' ? 'actif.' : 'suspendu.'));
    }

    /**
     * Changer le statut d'une agence (Actif / Bloqué) pour le contrôle global.
     */
    public function toggleStatus(Agence $agence)
    {
        $agence->statut = $agence->statut === 'Actif' ? 'Bloqué' : 'Actif';
        $agence->save();
        Audit::log('superadmin.agence.statut', $agence, ['statut' => $agence->statut]);

        return redirect()
            ->back()
            ->with('success', "Le statut de l'agence {$agence->nom_agence} a été mis à jour : {$agence->statut}.");
    }

    // ------------------------------------------------------------------

    private function creerAdmin(Agence $agence, string $nom, string $prenom, string $telephone, string $motDePasse): Utilisateur
    {
        return Utilisateur::create([
            'agence_id'        => $agence->id,
            'nom'              => $nom,
            'prenom'           => $prenom,
            'telephone'        => $telephone,
            'role'             => 'Admin',
            'mot_de_passe'     => Hash::make($motDePasse),
            'code_acces_agent' => null,
            'statut'           => 'actif',
            'compte_confirme'  => true,
        ]);
    }

    /** Mot de passe temporaire lisible (sans caractères ambigus). */
    public static function motDePasseTemporaire(): string
    {
        $alphabet = 'ABCDEFGHJKMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789';
        $mdp = '';
        for ($i = 0; $i < 10; $i++) {
            $mdp .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }

        return $mdp;
    }
}
