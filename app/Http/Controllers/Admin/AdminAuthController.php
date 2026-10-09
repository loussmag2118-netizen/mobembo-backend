<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AdminAuthController extends Controller
{
    public function showLoginForm()
    {
        return view('admin.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'telephone' => ['required', 'string'],
            'password'  => ['required', 'string'],
        ]);

        // ⭐ Tolérance : si l'utilisateur tape "+242..." ou "242...", on enlève
        // pour matcher le format local "06..." stocké en base.
        // Exemples :
        //   "064313808"      → "064313808"  (inchangé)
        //   "+242064313808"  → "064313808"
        //   "242064313808"   → "064313808"
        $telephone = trim($credentials['telephone']);
        $telephone = preg_replace('/^\+?242/', '', $telephone);

        $attempt = Auth::attempt([
            'telephone' => $telephone,
            'password'  => $credentials['password'],
        ]);

        if ($attempt) {
            $user = Auth::user();

            // Vérification du rôle : Admin ou Agent uniquement
            if (!in_array($user->role, ['Admin', 'Agent'])) {
                Auth::logout();
                throw ValidationException::withMessages([
                    'telephone' => 'Accès refusé. Vous n\'avez pas les droits d\'administration.',
                ]);
            }

            // Vérification du statut du compte
            if ($user->statut && strtolower($user->statut) !== 'actif') {
                Auth::logout();
                throw ValidationException::withMessages([
                    'telephone' => 'Ce compte a été désactivé par l\'administration.',
                ]);
            }

            // Vérification du statut de l'agence
            if ($user->agence_id && $user->agence && $user->agence->statut !== 'Actif') {
                Auth::logout();
                throw ValidationException::withMessages([
                    'telephone' => 'L\'agence à laquelle vous êtes rattaché est bloquée. Contactez le super administrateur.',
                ]);
            }

            $request->session()->regenerate();

            return redirect()->intended('/admin/dashboard');
        }

        throw ValidationException::withMessages([
            'telephone' => 'Les identifiants fournis sont incorrects.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')->with('success', 'Déconnexion réussie.');
    }
}