<?php

namespace App\Http\Controllers;

use App\Models\Utilisateur;
use App\Support\JetonReinitialisation;
use App\Support\SuppressionCompte;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /** Date à partir de laquelle un client doit confirmer son numéro avant de se connecter. */
    public const CONFIRMATION_OBLIGATOIRE_DEPUIS = '2026-10-15 00:00:00';

    /**
     * Inscription d'un nouvel utilisateur (Client, Admin, Agent).
     *
     * ⚠️ Le champ 'telephone' est stocké tel qu'envoyé par Flutter,
     * au format local (ex: 061234567, 9 chiffres avec le 0).
     * Ce même format doit être utilisé partout : register, login,
     * et dans OtpController (send/verify).
     */
    public function register(Request $request)
    {
        // L'inscription publique crée uniquement des comptes clients : les
        // administrateurs et agents sont créés depuis l'administration.
        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:255',
            'prenom' => 'nullable|string|max:255',
            'email' => 'nullable|email',
            'telephone' => ['required', 'string', 'regex:/^0[4-6]\d{7}$/'],
            'mot_de_passe' => 'required|string|min:8',
            // Les nouvelles versions de l'application envoient la confirmation
            'mot_de_passe_confirmation' => 'sometimes|same:mot_de_passe',
        ], [
            'telephone.regex' => 'Le numéro doit contenir 9 chiffres et commencer par 04, 05 ou 06.',
            'mot_de_passe.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'mot_de_passe_confirmation.same' => 'Les mots de passe ne correspondent pas.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        // Numéro déjà pris par un compte confirmé (ou du personnel) : refusé.
        // Compte client jamais confirmé (code non saisi) : la nouvelle
        // inscription le remplace, le numéro n'est pas bloqué.
        $existant = Utilisateur::withTrashed()->where('telephone', $request->telephone)->first();
        if ($existant && ($existant->trashed() || $existant->compte_confirme || $existant->role !== 'Client')) {
            return response()->json(['message' => 'Ce numéro de téléphone est déjà utilisé.'], 422);
        }
        if ($request->filled('email') && Utilisateur::where('email', $request->email)->when($existant, fn ($q) => $q->whereKeyNot($existant->id))->exists()) {
            return response()->json(['message' => 'Cette adresse e-mail est déjà utilisée.'], 422);
        }

        $donnees = [
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'email' => $request->email,
            'mot_de_passe' => Hash::make($request->mot_de_passe),
        ];
        $user = $existant
            ? tap($existant)->update($donnees)
            : Utilisateur::create($donnees + [
                'telephone' => $request->telephone,
                'role' => 'Client',
                'agence_id' => null,
                'compte_confirme' => false,
                'statut' => 'actif',
            ]);

        // Pas de jeton de connexion tant que le numéro n'est pas confirmé
        return response()->json([
            'message' => 'Inscription enregistrée. Confirmez votre numéro avec le code reçu.',
            'user' => $user->only(['id', 'nom', 'prenom', 'telephone', 'role']),
        ], 201);
    }

    /**
     * Connexion d'un utilisateur par téléphone et mot de passe.
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'telephone' => 'required|string',
            'mot_de_passe' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = Utilisateur::with(['agence', 'gare'])  // gare (et sa ville) : utile aux agents
            ->where('telephone', $request->telephone)
            ->first();

        // Vérification de l'existence de l'utilisateur et du mot de passe
        if (!$user || !Hash::check($request->mot_de_passe, $user->mot_de_passe)) {
            return response()->json([
                'message' => 'Numéro de téléphone ou mot de passe incorrect',
            ], 401);
        }

        // Vérification du statut assouplie (ignore la casse et autorise si le champ n'est pas explicitement désactivé)
        if ($user->statut && strtolower($user->statut) !== 'actif') {
            return response()->json([
                'message' => 'Ce compte a été désactivé par l\'administration.',
            ], 403);
        }

        // Client inscrit sans avoir saisi le code reçu : le numéro doit être
        // confirmé avant la première connexion (comptes créés depuis la mise
        // en place de cette règle ; les anciens comptes ne sont pas bloqués).
        if ($user->role === 'Client' && ! $user->compte_confirme
            && $user->created_at && $user->created_at->gte(self::CONFIRMATION_OBLIGATOIRE_DEPUIS)) {
            return response()->json([
                'message' => 'Votre numéro n\'est pas encore confirmé. Saisissez le code reçu pour activer votre compte.',
                'code' => 'COMPTE_NON_CONFIRME',
            ], 403);
        }

        // Suppression des anciens tokens pour éviter l'accumulation
        $user->tokens()->delete();

        $token = $user->createToken('mobembo_token')->plainTextToken;

        return response()->json([
            'message' => 'Connexion réussie',
            'user' => $user,
            'token' => $token,
        ], 200);
    }

    /**
     * Suppression du compte par le client lui-même (mot de passe redemandé).
     * DELETE /api/compte
     */
    public function supprimerCompte(Request $request)
    {
        $user = $request->user();
        $request->validate(['mot_de_passe' => 'required|string'], ['mot_de_passe.required' => 'Saisissez votre mot de passe pour confirmer.']);

        if ($user->role !== 'Client') {
            return response()->json(['message' => 'Les comptes du personnel sont supprimés par l\'administration de l\'agence.'], 403);
        }
        if (! Hash::check($request->mot_de_passe, $user->mot_de_passe)) {
            return response()->json(['message' => 'Mot de passe incorrect.'], 422);
        }

        SuppressionCompte::client($user);

        return response()->json(['message' => 'Votre compte a été supprimé.']);
    }

    /**
     * Réinitialisation directe du mot de passe.
     */
    public function resetPassword(Request $request)
    {
        // Ancienne version de l'application : « mot_de_passe »
        if (! $request->filled('nouveau_mot_de_passe') && $request->filled('mot_de_passe')) {
            $request->merge(['nouveau_mot_de_passe' => $request->mot_de_passe]);
        }

        $validator = Validator::make($request->all(), [
            'telephone' => 'required|string',
            'jeton' => 'required|string',
            'nouveau_mot_de_passe' => 'required|string|min:8',
        ], [
            'jeton.required' => 'Vérifiez d\'abord votre numéro avec le code reçu.',
            'nouveau_mot_de_passe.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        // Preuve que le numéro a reçu le code (vérification OTP « RESET_PASSWORD »)
        if (! JetonReinitialisation::consommer($request->telephone, $request->jeton)) {
            return response()->json([
                'message' => 'Votre vérification a expiré. Recommencez avec un nouveau code.',
            ], 403);
        }

        $user = Utilisateur::where('telephone', $request->telephone)->first();
        if (! $user) {
            return response()->json(['message' => 'Aucun compte trouvé avec ce numéro.'], 404);
        }
        
        $user->update([
            'mot_de_passe' => Hash::make($request->nouveau_mot_de_passe),
        ]);

        // Invalider les tokens existants suite au changement de mot de passe
        $user->tokens()->delete();

        return response()->json([
            'message' => 'Mot de passe réinitialisé avec succès',
        ], 200);
    }

    /**
     * Récupération du profil de l'utilisateur connecté (Nécessite Sanctum).
     */
    public function profile(Request $request)
    {
        return response()->json([
            'user' => $request->user()->load(['agence', 'gare']),
        ], 200);
    }

    /**
     * Déconnexion (Suppression du token actuel).
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Déconnexion réussie',
        ], 200);
    }
}