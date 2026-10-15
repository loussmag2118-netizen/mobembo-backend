<?php

use Illuminate\Support\Facades\Route;

// ================================================================
// CONTRÔLEURS ADMIN D'AGENCE
// ================================================================
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\BusController;
use App\Http\Controllers\Admin\GareController;
use App\Http\Controllers\Admin\TrajetController;
use App\Http\Controllers\Admin\AgentController;
use App\Http\Controllers\Admin\TicketAdminController;
use App\Http\Controllers\Admin\StatistiqueController;
use App\Http\Controllers\Admin\PlanificationVoyageController;
use App\Http\Controllers\Admin\AgentHistoryController;
use App\Http\Controllers\Admin\VagueDepartController;
use App\Http\Controllers\Admin\AdminHistoriqueController;
use App\Http\Controllers\Admin\VoyageurController;

// ================================================================
// CONTRÔLEURS SUPER ADMIN
// ================================================================
use App\Http\Controllers\SuperAdmin\AgenceController as SuperAdminAgenceController;
use App\Http\Controllers\SuperAdmin\AuthController as SuperAdminAuthController;
use App\Http\Controllers\SuperAdmin\VilleController as SuperAdminVilleController;

// ================================================================
// CONTRÔLEURS CANADIAN AIRWAYS
// ================================================================
use App\Http\Controllers\Admin\Canadian\CanadianLoginController;
use App\Http\Controllers\Admin\Canadian\CanadianDashboardController;
use App\Http\Controllers\Admin\Canadian\VolController;
use App\Http\Controllers\Admin\Canadian\ReservationController;
use App\Http\Controllers\Admin\Canadian\FlotteController;
use App\Http\Controllers\Admin\Canadian\PaiementController;
use App\Http\Controllers\Admin\Canadian\PromoController;
use App\Http\Controllers\Admin\Canadian\NotificationController;
use App\Http\Controllers\Admin\Canadian\RapportController;
use App\Http\Controllers\Admin\Canadian\ParametreController;

// ⭐ NOUVEAUX CONTRÔLEURS CANADIAN
use App\Http\Controllers\Admin\Canadian\VilleController;
use App\Http\Controllers\Admin\Canadian\ExtraController;
use App\Http\Controllers\Admin\Canadian\ClasseTarifaireController;
use App\Http\Controllers\Admin\Canadian\PassagerController;

// ⭐ NOUVEAU CONTRÔLEUR DE CONNEXION UNIFIÉE
use App\Http\Controllers\Auth\UnifiedLoginController;

// ================================================================
// RACINE
// ================================================================
// La page d'accueil du site est le formulaire de connexion de l'administration
// (au lieu de la page par défaut de Laravel).
Route::get('/', [UnifiedLoginController::class, 'showLoginForm'])
    ->name('home');

// ================================================================
// 🔐 CONNEXION UNIFIÉE — Une seule page pour tous les admins
// ================================================================
// ⚠️ PAS de middleware 'guest' → permet à un utilisateur déjà connecté
// de revenir sur /login pour changer de compte.
// La sécurité (déconnexion automatique des 3 guards) est gérée
// dans UnifiedLoginController::login().
// ================================================================
Route::get('/login', [UnifiedLoginController::class, 'showLoginForm'])
    ->name('unified.login');

Route::post('/login', [UnifiedLoginController::class, 'login'])
    ->middleware('throttle:connexion')
    ->name('unified.login.submit');

// Déconnexion universelle (déconnecte les 3 guards)
Route::post('/logout', [UnifiedLoginController::class, 'logout'])
    ->name('unified.logout')
    ->middleware('auth:web,canadian,superadmin');

// ================================================================
// 🔄 REDIRECTIONS DES ANCIENNES URLs DE LOGIN → /login
// ================================================================
Route::get('/admin/login', fn () => redirect()->route('unified.login'));
Route::get('/superadmin/login', fn () => redirect()->route('unified.login'));
Route::get('/canadian/login', fn () => redirect()->route('unified.login'));

// ================================================================
// 🔄 REDIRECTIONS DES URLs DE BASE → DASHBOARD
// ================================================================
Route::get('/admin', fn () => redirect()->route('admin.dashboard'))
    ->middleware('auth');
Route::get('/canadian', fn () => redirect()->route('canadian.admin'))
    ->middleware('auth:canadian');
Route::get('/superadmin', fn () => redirect()->route('superadmin.agences.index'))
    ->middleware('auth:superadmin');

// ================================================================
// SUPER ADMIN (protégé) — guard superadmin
// ================================================================
Route::middleware('auth:superadmin')
    ->prefix('superadmin')
    ->name('superadmin.')
    ->group(function () {

        Route::post('/logout', [SuperAdminAuthController::class, 'logout'])
            ->name('logout');

        Route::get('/agences', [SuperAdminAgenceController::class, 'index'])
            ->name('agences.index');

        Route::post('/agences', [SuperAdminAgenceController::class, 'store'])
            ->name('agences.store');

        Route::patch('/agences/{agence}/toggle-status', [SuperAdminAgenceController::class, 'toggleStatus'])
            ->name('agences.toggleStatus');

        // Référentiel des villes du transport terrestre (utilisé par les gares des agences)
        Route::patch('/villes/{ville}/toggle', [SuperAdminVilleController::class, 'toggle'])
            ->name('villes.toggle');
        Route::resource('villes', SuperAdminVilleController::class)
            ->only(['index', 'store', 'update', 'destroy']);
    });

// ================================================================
// ADMIN D'AGENCE (protégé) — Transport Terrestre
// ================================================================
Route::middleware(['auth', 'agence.active'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

        Route::get('/historique', [AdminHistoriqueController::class, 'index'])
            ->name('historique.index');
        Route::get('/historique/{id}', [AdminHistoriqueController::class, 'show'])
            ->name('historique.show');

        // --- Gestion : réservée aux administrateurs de l'agence
        Route::middleware('role:Admin')->group(function () {
            Route::get('/bus/trashed', [BusController::class, 'trashed'])->name('bus.trashed');
            Route::post('/bus/{id}/restore', [BusController::class, 'restore'])->name('bus.restore');
            Route::delete('/bus/{id}/force-delete', [BusController::class, 'forceDelete'])->name('bus.forceDelete');
            Route::resource('bus', BusController::class);

            Route::get('/gares/trashed', [GareController::class, 'trashed'])->name('gares.trashed');
            Route::post('/gares/{id}/restore', [GareController::class, 'restore'])->name('gares.restore');
            Route::delete('/gares/{id}/force-delete', [GareController::class, 'forceDelete'])->name('gares.forceDelete');
            Route::get('/gares/{gare}/historique', [GareController::class, 'historique'])->name('gares.historique');
            Route::resource('gares', GareController::class);

            Route::get('/trajets/trashed', [TrajetController::class, 'trashed'])->name('trajets.trashed');
            Route::post('/trajets/{id}/restore', [TrajetController::class, 'restore'])->name('trajets.restore');
            Route::delete('/trajets/{id}/force-delete', [TrajetController::class, 'forceDelete'])->name('trajets.forceDelete');
            Route::resource('trajets', TrajetController::class);

            Route::get('/vagues/trashed', [VagueDepartController::class, 'trashed'])->name('vagues.trashed');
            Route::post('/vagues/{id}/restore', [VagueDepartController::class, 'restore'])->name('vagues.restore');
            Route::delete('/vagues/{id}/force-delete', [VagueDepartController::class, 'forceDelete'])->name('vagues.forceDelete');
            Route::resource('vagues', VagueDepartController::class);

            Route::get('/agents/trashed', [AgentController::class, 'trashed'])->name('agents.trashed');
            Route::post('/agents/{id}/restore', [AgentController::class, 'restore'])->name('agents.restore');
            Route::delete('/agents/{id}/force-delete', [AgentController::class, 'forceDelete'])->name('agents.forceDelete');
            Route::get('/agents/{agent}/historique', [AgentHistoryController::class, 'show'])->name('agents.historique');
            Route::resource('agents', AgentController::class);

            Route::resource('planifications', PlanificationVoyageController::class)
                ->except(['index', 'show'])
                ->parameters(['planifications' => 'planificationVoyage']);

            Route::post('/voyageurs/{id}/rembourser', [VoyageurController::class, 'rembourser'])->name('voyageurs.rembourser');
            Route::resource('voyageurs', VoyageurController::class)->except(['index', 'show']);
            Route::resource('statistiques', StatistiqueController::class);
            Route::patch('/agence/age-enfant', [PlanificationVoyageController::class, 'majAgeEnfant'])->name('agence.age_enfant');
            Route::patch('/agence/choix-siege', [PlanificationVoyageController::class, 'majChoixSiege'])->name('agence.choix_siege');
        });

        // --- Lecture : accessible aux agents (guichet / contrôle) et aux admins
        Route::middleware('role:Admin,Agent')->group(function () {
            Route::resource('planifications', PlanificationVoyageController::class)
                ->only(['index', 'show'])
                ->parameters(['planifications' => 'planificationVoyage']);
            Route::resource('voyageurs', VoyageurController::class)->only(['index', 'show']);
            Route::resource('tickets', TicketAdminController::class)->only(['index']);
            Route::get('/planifications/{planificationVoyage}/sieges', [PlanificationVoyageController::class, 'sieges'])
                ->name('planifications.sieges');
        });
    });

// ================================================================
// CANADIAN AIRWAYS (protégé par guard canadian)
// ================================================================
Route::prefix('canadian')
    ->middleware(['auth:canadian'])
    ->name('canadian.')
    ->group(function () {

        Route::get('/admin', [CanadianDashboardController::class, 'index'])->name('admin');

        Route::resource('vols', VolController::class);
        Route::post('vols/{id}/dupliquer', [VolController::class, 'dupliquer'])->name('vols.dupliquer');

        Route::get('reservations', [ReservationController::class, 'index'])->name('reservations.index');
        Route::get('reservations/{id}', [ReservationController::class, 'show'])->name('reservations.show');
        Route::post('reservations/{id}/annuler', [ReservationController::class, 'annuler'])->name('reservations.annuler');
        Route::post('reservations/{id}/rembourser', [ReservationController::class, 'rembourser'])->name('reservations.rembourser');
        Route::post('reservations/{id}/renvoyer-billet', [ReservationController::class, 'renvoyerBillet'])->name('reservations.renvoyerBillet');
        Route::patch('reservations/{id}/statut', [ReservationController::class, 'modifierStatut'])->name('reservations.modifierStatut');

        Route::get('paiements', [PaiementController::class, 'index'])->name('paiements.index');
        Route::get('paiements/export/csv', [PaiementController::class, 'export'])->name('paiements.export');
        Route::get('paiements/{id}', [PaiementController::class, 'show'])->name('paiements.show');

        Route::resource('flotte', FlotteController::class);

        Route::patch('promos/{id}/toggle', [PromoController::class, 'toggle'])->name('promos.toggle');
        Route::resource('promos', PromoController::class);

        Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::post('notifications', [NotificationController::class, 'store'])->name('notifications.store');
        Route::delete('notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');

        Route::resource('villes', VilleController::class)->except(['show']);
        Route::resource('extras', ExtraController::class)->except(['show']);
        Route::resource('classes', ClasseTarifaireController::class)->except(['show']);

        Route::get('passagers', [PassagerController::class, 'index'])->name('passagers.index');
        Route::get('passagers/{id}', [PassagerController::class, 'show'])->name('passagers.show');

        Route::get('rapports', [RapportController::class, 'index'])->name('rapports.index');
        Route::get('rapports/generer', [RapportController::class, 'generer'])->name('rapports.generer');
        Route::get('rapports/export', [RapportController::class, 'exportPdfExcel'])->name('rapports.export');

        Route::get('parametres', [ParametreController::class, 'index'])->name('parametres.index');
        Route::post('parametres', [ParametreController::class, 'update'])->name('parametres.update');
    });