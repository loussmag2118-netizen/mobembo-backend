<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',

        commands: __DIR__.'/../routes/console.php',
        health: '/up',

    )
    ->withMiddleware(function (Middleware $middleware) {
        // ⭐ Cloud Run : faire confiance au proxy pour que Laravel voie le
        // HTTPS d'origine (liens, redirections et retours de paiement en https)
        $middleware->trustProxies(
            at: '*',
            headers: Request::HEADER_X_FORWARDED_FOR
                | Request::HEADER_X_FORWARDED_HOST
                | Request::HEADER_X_FORWARDED_PORT
                | Request::HEADER_X_FORWARDED_PROTO,
        );

        // ⭐ Alias des middlewares personnalisés
        $middleware->alias([
            'agence.active' => \App\Http\Middleware\CheckAgenceActive::class,
        ]);

        // ⭐ Redirection des invités (non connectés) → page /login unifiée
        $middleware->redirectGuestsTo(function (\Illuminate\Http\Request $request) {
            return route('unified.login');
        });

        // ⭐ Redirection des utilisateurs DÉJÀ connectés
        // ⚠️ IMPORTANT : on vérifie d'abord le guard, sans regarder l'URL
        $middleware->redirectUsersTo(function (\Illuminate\Http\Request $request) {
            // 1. Super Admin connecté ?
            if (auth('superadmin')->check()) {
                return route('superadmin.agences.index');
            }

            // 2. Canadian connecté ?
            if (auth('canadian')->check()) {
                return route('canadian.admin');
            }

            // 3. Admin agence / Agent connecté ?
            if (auth('web')->check()) {
                return route('admin.dashboard');
            }

            // 4. Par défaut, rester sur la page de login
            return route('unified.login');
        });

        // ⭐ TEMPORAIRE — Exclure le login du CSRF pour diagnostiquer
        // ⚠️ À RETIRER une fois que tout fonctionne
        $middleware->validateCsrfTokens(except: [
            'login',
            'superadmin/login',
            'superadmin/login/*',
            'canadian/login',
            'admin/login',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();
