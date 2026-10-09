<?php

namespace App\Http\Controllers;

use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
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
        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:255',
            'prenom' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:utilisateurs,email',
            'telephone' => 'required|string|size:9|unique:utilisateurs,telephone',
            'mot_de_passe' => 'required|string|min:6',
            'role' => 'nullable|in:Client,Admin,Agent',
            'agence_id' => 'nullable|exists:agences,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = Utilisateur::create([
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'email' => $request->email,
            'telephone' => $request->telephone,
            'mot_de_passe' => Hash::make($request->mot_de_passe),
            'role' => $request->role ?? 'Client',
            'agence_id' => $request->agence_id,
            'compte_confirme' => false,
            'statut' => 'actif',
        ]);

        $token = $user->createToken('mobembo_token')->plainTextToken;

        return response()->json([
            'message' => 'Inscription réussie',
            'user' => $user->load('agence'),
            'token' => $token,
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

        $user = Utilisateur::with('agence')
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
     * Réinitialisation directe du mot de passe.
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'telephone' => 'required|string|exists:utilisateurs,telephone',
            'nouveau_mot_de_passe' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = Utilisateur::where('telephone', $request->telephone)->first();
        
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
            'user' => $request->user()->load('agence'),
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