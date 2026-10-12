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
use App\Http\Controllers\Api\MomoController;
use App\Http\Controllers\Api\AirtelMoneyController;

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
Route::post('/register',        [AuthController::class, 'register']);
Route::post('/login',           [AuthController::class, 'login']);
Route::post('/reset-password',  [AuthController::class, 'resetPassword']);

// ============================================================
// ROUTES PUBLIQUES — Canadian Airways
// ============================================================
Route::post('/canadian/register',       [CanadianAuthController::class, 'register']);
Route::post('/canadian/login',          [CanadianAuthController::class, 'login']);
Route::post('/canadian/reset-password', [CanadianAuthController::class, 'resetPassword']);

// ============================================================
// ROUTES PUBLIQUES — OTP
// ============================================================
Route::post('/send-otp',   [OtpController::class, 'send']);
Route::post('/verify-otp', [OtpController::class, 'verify']);

Route::post('/canadian/send-otp',   [OtpController::class, 'send']);
Route::post('/canadian/verify-otp', [OtpController::class, 'verify']);

Route::post('/send-otp-dev', [OtpController::class, 'sendDev']);

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
Route::middleware('auth:sanctum')->group(function () {

    // Profil
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Upload d'images (Google Cloud Storage)
    Route::post('/upload', [ImageController::class, 'upload']);

    // Paiements mobile
    Route::post('/momo/pay',   [MomoController::class, 'requestToPay']);
    Route::post('/airtel/pay', [AirtelMoneyController::class, 'requestToPay']);

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