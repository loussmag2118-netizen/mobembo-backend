<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\CanadianUser;
use App\Models\SuperAdmin;
use App\Models\Utilisateur;
use App\Support\Audit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UnifiedLoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.unified-login');
    }

    public function login(Request $request)
    {
        // ============================================================
        // ⭐ SÉCURITÉ : Déconnecter TOUS les guards avant toute nouvelle connexion
        // Cela empêche un utilisateur déjà connecté de rester sur sa session
        // précédente en entrant les identifiants d'un autre compte.
        // ============================================================
        Auth::guard('web')->logout();
        Auth::guard('canadian')->logout();
        Auth::guard('superadmin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // On réinjecte le token CSRF pour que la validation passe
        $request->session()->put('_token', $request->input('_token'));

        // ============================================================
        // Validation des champs
        // ============================================================
        $request->validate([
            'identifiant' => ['required', 'string'],
            'password'    => ['required', 'string'],
        ]);

        $identifiant = trim($request->input('identifiant'));
        $password    = $request->input('password');

        // Normalisation téléphone (retire +242 / 242 en préfixe)
        $telNormalise = preg_replace('/^\+?242/', '', $identifiant);
        $telAvecZero  = '0' . ltrim($telNormalise, '0');
        $telAvecIndic = '+242' . ltrim($telNormalise, '0');

        // ============================================================
        // 1️⃣ ESSAI SUPER ADMIN — ⚠️ uniquement par EMAIL
        // (la table super_admins n'a PAS de colonne telephone)
        // ============================================================
        $superAdmin = SuperAdmin::where('email', $identifiant)->first();

        if ($superAdmin && Hash::check($password, $superAdmin->mot_de_passe)) {
            Auth::guard('superadmin')->login($superAdmin);
            $request->session()->regenerate();
            Audit::log('connexion.reussie', $superAdmin, ['espace' => 'superadmin']);

            return redirect()->intended(route('superadmin.dashboard'));
        }

        // ============================================================
        // 2️⃣ ESSAI CANADIAN — email ou téléphone, mot de passe = "password"
        // ============================================================
        $canadian = CanadianUser::where(function ($q) use ($identifiant, $telNormalise, $telAvecZero, $telAvecIndic) {
                $q->where('email', $identifiant)
                  ->orWhere('telephone', $identifiant)
                  ->orWhere('telephone', $telNormalise)
                  ->orWhere('telephone', $telAvecZero)
                  ->orWhere('telephone', $telAvecIndic);
            })
            ->first();

        if ($canadian && Hash::check($password, $canadian->password)) {
            Auth::guard('canadian')->login($canadian, $request->boolean('remember'));
            $request->session()->regenerate();
            Audit::log('connexion.reussie', $canadian, ['espace' => 'canadian']);

            return redirect()->intended(route('canadian.admin'));
        }

        // ============================================================
        // 3️⃣ ESSAI UTILISATEUR — ⚠️ mot de passe = "mot_de_passe"
        // ============================================================
        $user = Utilisateur::where(function ($q) use ($identifiant, $telNormalise, $telAvecZero, $telAvecIndic) {
                $q->where('telephone', $telNormalise)
                  ->orWhere('telephone', $telAvecZero)
                  ->orWhere('telephone', $telAvecIndic)
                  ->orWhere('telephone', $identifiant)
                  ->orWhere('email', $identifiant);
            })
            ->first();

        if ($user && Hash::check($password, $user->mot_de_passe)) {

            // Vérification du rôle
            if (!in_array($user->role, ['Admin', 'Agent'])) {
                throw ValidationException::withMessages([
                    'identifiant' => 'Accès refusé. Vous n\'avez pas les droits d\'administration.',
                ]);
            }

            // Vérification du statut du compte
            if ($user->statut && strtolower($user->statut) !== 'actif') {
                throw ValidationException::withMessages([
                    'identifiant' => 'Ce compte a été désactivé par l\'administration.',
                ]);
            }

            // Vérification du statut de l'agence
            if ($user->agence_id && $user->agence && $user->agence->statut !== 'Actif') {
                throw ValidationException::withMessages([
                    'identifiant' => 'L\'agence à laquelle vous êtes rattaché est bloquée. Contactez le super administrateur.',
                ]);
            }

            Auth::guard('web')->login($user, $request->boolean('remember'));
            $request->session()->regenerate();
            Audit::log('connexion.reussie', $user, ['espace' => 'admin', 'role' => $user->role]);

            return redirect()->intended('/admin/dashboard');
        }

        // ============================================================
        // ❌ AUCUN COMPTE TROUVÉ
        // ============================================================
        Audit::log('connexion.echouee', null, ['identifiant' => mb_substr($identifiant, 0, 80)]);

        throw ValidationException::withMessages([
            'identifiant' => 'Les identifiants fournis sont incorrects.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        Auth::guard('canadian')->logout();
        Auth::guard('superadmin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('unified.login')->with('success', 'Déconnexion réussie.');
    }
}