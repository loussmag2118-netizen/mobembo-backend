<?php

use Illuminate\Support\Facades\Route;

// ============================================================
// CONTRÔLEURS AUTHENTIFICATION & OTP
// ============================================================
use App\Http\Controllers\AuthController;
use App\Http\Controllers\OtpController;

// ============================================================
// CONTRÔLEUR UPLOAD D'IMAGES (Google Cloud Storage)
// ============================================================
use App\Http\Controllers\ImageController;

// ============================================================
// CONTRÔLEURS PAIEMENT MOBILE (dans Api/)
// ============================================================
use App\Http\Controllers\Api\PaiementBusController;
use App\Http\Controllers\Api\ClientBusController;

// ============================================================
// CONTRÔLEURS API — AGENT MOBILE
// ============================================================
use App\Http\Controllers\Api\AgentVoyageursController;
use App\Http\Controllers\Api\AgentPresenceController;

// ============================================================
// CONTRÔLEURS CANADIAN AIRWAYS
// ============================================================
use App\Http\Controllers\Api\CanadianAuthController;
use App\Http\Controllers\Api\CanadianClientController;
use App\Http\Controllers\Api\CanadianCarnetController;
use App\Http\Controllers\Api\VolApiController; // ⭐ AJOUTÉ

// ============================================================
// CONTRÔLEURS ADMIN (pour les méthodes apiIndex)
// ============================================================
use App\Http\Controllers\Admin\GareController;
use App\Http\Controllers\Admin\TrajetController;
use App\Http\Controllers\Admin\PlanificationVoyageController;
use App\Http\Controllers\Admin\TicketAdminController;

/*
|--------------------------------------------------------------------------
| API Routes — Mobembo Line
|--------------------------------------------------------------------------
*/

// ============================================================
// ROUTES PUBLIQUES — Authentification Mobembo Line
// ============================================================
Route::post('/register',        [AuthController::class, 'register'])->middleware('throttle:connexion');
Route::post('/login',           [AuthController::class, 'login'])->middleware('throttle:connexion');
Route::post('/reset-password',  [AuthController::class, 'resetPassword'])->middleware('throttle:connexion');

// ============================================================
// ROUTES PUBLIQUES — Canadian Airways
// ============================================================
Route::post('/canadian/register',       [CanadianAuthController::class, 'register'])->middleware('throttle:connexion');
Route::post('/canadian/login',          [CanadianAuthController::class, 'login'])->middleware('throttle:connexion');
Route::post('/canadian/reset-password', [CanadianAuthController::class, 'resetPassword'])->middleware('throttle:connexion');

// ============================================================
// ROUTES PUBLIQUES — OTP
// ============================================================
Route::post('/send-otp',   [OtpController::class, 'send'])->middleware('throttle:otp');
Route::post('/verify-otp', [OtpController::class, 'verify'])->middleware('throttle:connexion');

Route::post('/canadian/send-otp',   [OtpController::class, 'send'])->middleware('throttle:otp');
Route::post('/canadian/verify-otp', [OtpController::class, 'verify'])->middleware('throttle:connexion');

Route::post('/send-otp-dev', [OtpController::class, 'sendDev'])->middleware('throttle:otp');

// ============================================================
// VÉRIFICATION DU NUMÉRO AVANT INSCRIPTION
// ============================================================
Route::get('/check-phone', function (\Illuminate\Http\Request $request) {
    $phone = trim($request->query('telephone', ''));

    if (empty($phone)) {
        return response()->json([
            'message' => 'Paramètre "telephone" manquant.',
        ], 400);
    }

    $existe = \App\Models\Utilisateur::where('telephone', $phone)->exists();

    return response()->json([
        'telephone' => $phone,
        'existe'    => $existe,
    ]);
});

// ============================================================
// CONTRÔLEURS API — AGENCES MOBILES
// ============================================================
use App\Http\Controllers\Api\AgenceApiController;

// ============================================================
// ROUTES PUBLIQUES — Consultation Mobile (JSON)
// ============================================================
Route::get('/agences',        [AgenceApiController::class, 'index']);
Route::get('/gares',          [GareController::class, 'apiIndex']);
Route::get('/trajets',        [TrajetController::class, 'apiIndex']);
Route::get('/planifications', [PlanificationVoyageController::class, 'apiIndex']);
Route::get('/agence-donnees', [TrajetController::class, 'apiAgenceDonnees']);

// Recherche de voyages toutes agences confondues
Route::get('/villes-terrestres', [ClientBusController::class, 'villes']);
Route::get('/voyages/recherche', [ClientBusController::class, 'rechercher'])->middleware('throttle:60,1');
Route::get('/departs/{id}/sieges', [ClientBusController::class, 'sieges'])->whereNumber('id')->middleware('throttle:120,1');

// ============================================================
// ⭐ ROUTES PUBLIQUES — Recherche de vols Canadian Airways
// (Accessibles SANS connexion pour permettre la recherche libre)
// ============================================================
Route::get('/vols/search', [VolApiController::class, 'search']);
Route::get('/vols/villes', [VolApiController::class, 'villes']); // ⭐ Bonus : liste des villes

// ============================================================
// ⭐ ROUTES CLIENT CANADIAN (protégées par Sanctum + guard canadian-api)
// ============================================================
Route::middleware('auth:canadian-api')->prefix('canadian')->group(function () {

    // ────────────────────────────────────────────────────────
    // Profil & déconnexion
    // ────────────────────────────────────────────────────────
    Route::get('/profile',  [CanadianClientController::class, 'profile']);
    Route::post('/logout',  [CanadianClientController::class, 'logout']);
    Route::delete('/compte', [CanadianClientController::class, 'supprimerCompte'])->middleware('throttle:connexion');

    // ────────────────────────────────────────────────────────
    // Vols
    // ────────────────────────────────────────────────────────
    Route::get('/villes-vols',     [CanadianClientController::class, 'villesVols']);
    Route::post('/recherche-vols', [CanadianClientController::class, 'rechercheVols']);

    // ────────────────────────────────────────────────────────
    // Réservations
    // ────────────────────────────────────────────────────────
    Route::get('/mes-reservations',            [CanadianClientController::class, 'mesReservations']);
    Route::post('/reservations',               [CanadianClientController::class, 'creerReservation']);
    Route::get('/reservations/{id}',           [CanadianClientController::class, 'showReservation']);
    Route::post('/reservations/{id}/annuler',   [CanadianClientController::class, 'annulerReservation']);
    Route::get('/reservations/{id}/billet',     [CanadianClientController::class, 'billetPdf']);

    // ────────────────────────────────────────────────────────
    // Extras disponibles
    // ────────────────────────────────────────────────────────
    Route::get('/extras', [CanadianClientController::class, 'listExtras']);

    // ────────────────────────────────────────────────────────
    // Paiements
    // ────────────────────────────────────────────────────────
    Route::post('/paiements/initier', [CanadianClientController::class, 'initierPaiement']);

    // ────────────────────────────────────────────────────────
    // Notifications
    // ────────────────────────────────────────────────────────
    Route::get('/notifications',          [CanadianClientController::class, 'notifications']);
    Route::patch('/notifications/{id}/lu', [CanadianClientController::class, 'marquerNotificationLue']);

    // ────────────────────────────────────────────────────────
    // ⭐ Carnet de passagers (favoris)
    // ────────────────────────────────────────────────────────
    Route::get('/carnet-passagers',         [CanadianCarnetController::class, 'index']);
    Route::post('/carnet-passagers',        [CanadianCarnetController::class, 'store']);
    Route::get('/carnet-passagers/{id}',    [CanadianCarnetController::class, 'show']);
    Route::patch('/carnet-passagers/{id}',  [CanadianCarnetController::class, 'update']);
    Route::delete('/carnet-passagers/{id}', [CanadianCarnetController::class, 'destroy']);
});

// ============================================================
// ROUTES PROTÉGÉES (auth:sanctum) — Mobembo Line
// ============================================================
// Retours des opérateurs de paiement : le contenu n'est pas cru sur parole,
// le statut est revérifié auprès de l'API de l'opérateur.
Route::match(['post', 'put'], '/paiements/mtn/callback', [PaiementBusController::class, 'callbackMtn'])->middleware('throttle:120,1');
Route::post('/paiements/airtel/callback', [PaiementBusController::class, 'callbackAirtel'])->middleware('throttle:120,1');

Route::middleware('auth:sanctum')->group(function () {

    // Profil
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::delete('/compte', [AuthController::class, 'supprimerCompte'])->middleware('throttle:connexion');

    // Upload d'images (Google Cloud Storage)
    Route::post('/upload', [ImageController::class, 'upload']);

    // Paiements mobile
    Route::middleware('throttle:paiement')->group(function () {
        Route::post('/momo/pay',   [PaiementBusController::class, 'payerMtn']);
        Route::post('/airtel/pay', [PaiementBusController::class, 'payerAirtel']);
    });

    // Billets du client connecté
    Route::get('/mes-billets', [ClientBusController::class, 'mesBillets']);

    // Suivi d'un paiement par le client (propriétaire uniquement)
    Route::get('/paiements/{reference}/statut',   [PaiementBusController::class, 'statut'])->middleware('throttle:60,1');
    Route::post('/paiements/{reference}/annuler', [PaiementBusController::class, 'annuler'])->middleware('throttle:20,1');

    // Scan & embarquement
    Route::post('/scan-ticket',         [TicketAdminController::class, 'scan']);
    Route::post('/update-embarquement', [TicketAdminController::class, 'updateEmbarquement']);

    // Agent mobile
    Route::prefix('agent')->group(function () {
        Route::get('/voyageurs', [AgentVoyageursController::class, 'index'])
            ->name('api.agent.voyageurs');

        Route::post('/update-presence', [AgentPresenceController::class, 'update'])
            ->name('api.agent.updatePresence');

        Route::get('/ticket/{reference}', [AgentPresenceController::class, 'showByReference'])
            ->name('api.agent.ticketByReference');
    });
});