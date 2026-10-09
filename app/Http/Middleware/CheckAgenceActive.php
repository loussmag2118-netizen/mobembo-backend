<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckAgenceActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user) {
            return $next($request);
        }

        // Compte utilisateur désactivé
        if ($user->statut && strtolower($user->statut) !== 'actif') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('unified.login')
                ->with('error', 'Ce compte a été désactivé par l\'administration.');
        }

        // Agence bloquée
        if ($user->agence_id && $user->agence && $user->agence->statut !== 'Actif') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('unified.login')
                ->with('error', 'Votre agence est actuellement bloquée. Contactez le super administrateur.');
        }

        return $next($request);
    }
}