<?php

namespace App\Http\Controllers\Admin\Canadian;

use App\Http\Controllers\Controller;
use App\Models\CanadianUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CanadianLoginController extends Controller
{
    /**
     * Affiche la page de connexion.
     */
    public function showLoginForm()
    {
        return view('admin.canadian.auth.login');
    }

    /**
     * ⭐ Connexion : accepte EMAIL ou TÉLÉPHONE
     */
    public function login(Request $request)
    {
        Log::info('=== LOGIN CANADIAN START ===');
        Log::info('email reçu   : [' . $request->input('email') . ']');
        Log::info('password reçu: [' . $request->input('password') . ']');

        // ─────────────────────────────────────────────────────────────
        // 1. Validation
        // ─────────────────────────────────────────────────────────────
        $validator = Validator::make($request->all(), [
            'email'    => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:6'],
        ], [
            'email.required'    => 'L\'email ou le téléphone est obligatoire.',
            'email.string'      => 'L\'identifiant doit être une chaîne de caractères.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.min'      => 'Le mot de passe doit contenir au moins 6 caractères.',
        ]);

        if ($validator->fails()) {
            Log::info('VALIDATION FAILED: ' . json_encode($validator->errors()->toArray()));
            return back()
                ->withErrors($validator)
                ->onlyInput('email');
        }

        // ─────────────────────────────────────────────────────────────
        // 2. Normalisation
        // ─────────────────────────────────────────────────────────────
        $emailOuTel = trim($request->input('email'));
        $password   = $request->input('password');

        $telNormalise = preg_replace('/^\+?242/', '', $emailOuTel);
        $telAvecZero  = '0' . ltrim($telNormalise, '0');

        Log::info('Recherche user avec : emailOuTel=[' . $emailOuTel . '] telNormalise=[' . $telNormalise . '] telAvecZero=[' . $telAvecZero . ']');

        // ─────────────────────────────────────────────────────────────
        // 3. Recherche
        // ─────────────────────────────────────────────────────────────
        $user = CanadianUser::where('email', $emailOuTel)
            ->orWhere('telephone', $emailOuTel)
            ->orWhere('telephone', $telNormalise)
            ->orWhere('telephone', $telAvecZero)
            ->orWhere('telephone', '+242' . ltrim($telNormalise, '0'))
            ->first();

        Log::info('USER TROUVÉ: ' . ($user ? 'OUI id=' . $user->id . ' tel=' . $user->telephone . ' email=' . $user->email : 'NON'));

        if (!$user) {
            Log::info('→ USER NOT FOUND, on renvoie erreur');
            return back()->withErrors([
                'email' => 'Les identifiants fournis ne correspondent pas à nos enregistrements.',
            ])->onlyInput('email');
        }

        // ─────────────────────────────────────────────────────────────
        // 4. Vérification mot de passe
        // ─────────────────────────────────────────────────────────────
        $hashOk = Hash::check($password, $user->password);
        Log::info('HASH CHECK: ' . ($hashOk ? 'OUI' : 'NON'));
        Log::info('Hash en base (30 car.): ' . substr($user->password, 0, 30));

        if (!$hashOk) {
            Log::info('→ Mauvais mot de passe, on renvoie erreur');
            return back()->withErrors([
                'email' => 'Les identifiants fournis ne correspondent pas à nos enregistrements.',
            ])->onlyInput('email');
        }

        // ─────────────────────────────────────────────────────────────
        // 5. Connexion
        // ─────────────────────────────────────────────────────────────
        Auth::guard('canadian')->login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        Log::info('LOGIN OK → redirect vers ' . route('canadian.admin'));
        Log::info('=== LOGIN CANADIAN END (SUCCESS) ===');

        return redirect()->intended(route('canadian.admin'));
    }

    /**
     * Déconnexion.
     */
    public function logout(Request $request)
    {
        Auth::guard('canadian')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('canadian.login');
    }
}