<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SuperAdmin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('superadmin.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'telephone'    => ['required', 'string'],
            'mot_de_passe' => ['required'],
        ]);

        $saisie = trim($request->telephone);

        if (!str_starts_with($saisie, '+')) {
            $telephoneRecherche = str_starts_with($saisie, '0')
                ? '+242' . substr($saisie, 1)
                : '+242' . $saisie;
        } else {
            $telephoneRecherche = $saisie;
        }

        $user = SuperAdmin::where('telephone', $telephoneRecherche)
            ->orWhere('telephone', $saisie)
            ->first();

        if (!$user || !Hash::check($request->mot_de_passe, $user->mot_de_passe)) {
            return back()->withErrors([
                'telephone' => 'Identifiants incorrects.',
            ])->onlyInput('telephone');
        }

        // ⚠️ Utiliser le guard superadmin, pas le guard par défaut
        Auth::guard('superadmin')->login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('superadmin.agences.index'));
    }

    public function logout(Request $request)
    {
        Auth::guard('superadmin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('superadmin.login');
    }
}