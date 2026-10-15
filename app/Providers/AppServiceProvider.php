<?php

namespace App\Providers;

use App\Models\Bus;
use App\Models\Gare;
use App\Models\PlanificationVoyage;
use App\Models\Trajet;
use App\Models\Utilisateur;
use App\Models\VagueDepart;
use App\Observers\AuditObserver;
use App\Support\UrlPublique;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configurerLimitesDeTentatives();

        // Production : adresses générées avec le domaine public (APP_URL)
        if ($this->app->environment('production')) {
            UrlPublique::appliquer(config('app.url'));
        }

        // Pagination de l'administration (5 éléments par page)
        Paginator::defaultView('partials.pagination');

        // Journal d'audit des créations / modifications / suppressions
        // faites depuis l'administration
        foreach ([Bus::class, Gare::class, Trajet::class, VagueDepart::class, PlanificationVoyage::class, Utilisateur::class] as $modele) {
            $modele::observe(AuditObserver::class);
        }
    }

    /**
     * Limites contre les tentatives abusives (devinette de mot de passe,
     * envoi massif de SMS, rafales de paiements).
     */
    private function configurerLimitesDeTentatives(): void
    {
        // Connexion : 5 essais par minute pour un même identifiant et une même IP
        RateLimiter::for('connexion', function (Request $request) {
            $identifiant = strtolower((string) ($request->input('identifiant') ?? $request->input('telephone') ?? $request->input('email') ?? ''));

            return [
                Limit::perMinute(5)->by('connexion:' . $identifiant . '|' . $request->ip()),
                Limit::perMinute(30)->by('connexion-ip:' . $request->ip()),
            ];
        });

        // SMS de vérification : chaque SMS est facturé
        RateLimiter::for('otp', function (Request $request) {
            return [
                Limit::perMinute(3)->by('otp:' . $request->input('telephone') . '|' . $request->ip()),
                Limit::perHour(10)->by('otp-tel:' . $request->input('telephone')),
            ];
        });

        // Paiement : quelques demandes par minute et par client
        RateLimiter::for('paiement', function (Request $request) {
            return Limit::perMinute(6)->by('paiement:' . ($request->user()?->id ?? $request->ip()));
        });
    }
}
