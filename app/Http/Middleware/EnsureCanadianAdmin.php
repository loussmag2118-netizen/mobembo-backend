<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Administration Canadian Airways : réservée aux comptes est_admin.
 * Un client de l'application mobile (même table) est déconnecté.
 */
class EnsureCanadianAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $compte = Auth::guard('canadian')->user();

        if (! $compte || ! $compte->est_admin) {
            Auth::guard('canadian')->logout();

            return redirect()->route('unified.login')
                ->withErrors(['identifiant' => 'Accès réservé aux administrateurs de la compagnie.']);
        }

        return $next($request);
    }
}
