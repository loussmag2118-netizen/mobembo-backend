<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Autorise uniquement les rôles indiqués : ->middleware('role:Admin')
 * ou ->middleware('role:Admin,Agent').
 */
class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $role = $request->user()?->role;

        if (! in_array($role, $roles, true)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => "Vous n'avez pas les droits nécessaires pour cette action."], 403);
            }
            abort(403, "Vous n'avez pas les droits nécessaires pour cette action.");
        }

        return $next($request);
    }
}
