<?php

namespace App\Http\Middleware;

use App\Models\Agence;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Chaque partenaire n'entre que dans l'espace de son type :
 * compagnie de bus → /admin, compagnie aérienne → /aerien.
 *   ->middleware('espace:bus') ou ->middleware('espace:aerien')
 */
class EspacePartenaire
{
    public function handle(Request $request, Closure $next, string $type): Response
    {
        $agence = Auth::user()?->agence;
        $typeCompte = $agence?->type_transport ?? Agence::BUS;

        if ($typeCompte !== $type) {
            return redirect(self::accueil(Auth::user()));
        }

        return $next($request);
    }

    /** Tableau de bord du compte selon le type de son partenaire. */
    public static function accueil($utilisateur): string
    {
        return $utilisateur?->agence?->estAerien()
            ? route('canadian.admin')
            : route('admin.dashboard');
    }
}
