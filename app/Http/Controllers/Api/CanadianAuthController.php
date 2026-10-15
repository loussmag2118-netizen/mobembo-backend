<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CanadianUser;
use App\Support\JetonReinitialisation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CanadianAuthController extends Controller
{
    /**
     * ⭐ Inscription Canadian Airways
     */
    public function register(Request $request)
    {
        // Normalise les données reçues
        $nom        = $request->input('nom', $request->input('name', ''));
        $prenom     = $request->input('prenom', '');
        $telephone  = $request->input('telephone', '');
        $motDePasse = $request->input('mot_de_passe', $request->input('password', ''));

        // Génère un email si absent (basé sur le téléphone)
        $email = $request->input('email', '');
        if (empty($email) && !empty($telephone)) {
            $email = $telephone . '@canadian.local';
        }

        // Validation
        $validator = Validator::make([
            'nom'          => $nom,
            'prenom'       => $prenom,
            'telephone'    => $telephone,
            'mot_de_passe' => $motDePasse,
            'email'        => $email,
        ], [
            'nom'          => 'required|string|max:255',
            'prenom'       => 'nullable|string|max:255',
            // ⭐ REGEX : 9 chiffres, commence par 06, 04 ou 05
            'telephone'    => ['required', 'string', 'regex:/^(06|04|05)\d{7}$/', 'unique:canadian_users,telephone'],
            'mot_de_passe' => 'required|string|min:6',
            'email'        => 'required|string|email|max:255|unique:canadian_users,email',
        ], [
            'telephone.required'    => 'Le numéro de téléphone est obligatoire.',
            'telephone.regex'       => 'Le numéro doit commencer par 06, 04 ou 05 et contenir 9 chiffres.',
            'telephone.unique'      => 'Ce numéro est déjà utilisé.',
            'nom.required'          => 'Le nom est obligatoire.',
            'mot_de_passe.required' => 'Le mot de passe est obligatoire.',
            'mot_de_passe.min'      => 'Le mot de passe doit contenir au moins 6 caractères.',
            'email.unique'          => 'Cet email est déjà utilisé.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        // Création
        $user = CanadianUser::create([
            'name'            => trim($nom . ' ' . $prenom),
            'nom'             => $nom,
            'prenom'          => $prenom,
            'email'           => $email,
            'telephone'       => $telephone,
            'password'        => Hash::make($motDePasse),
            'compte_confirme' => false,
        ]);

        // Token Sanctum avec ability canadian-api
        $token = $user->createToken('canadian_token', ['canadian-api'])->plainTextToken;

        return response()->json([
            'message' => 'Inscription réussie',
            'user'    => $user,
            'token'   => $token,
        ], 201);
    }

    /**
     * ⭐ Connexion Canadian Airways
     */
    public function login(Request $request)
    {
        $emailOuTel = $request->input('email', $request->input('telephone', ''));
        $motDePasse = $request->input('password', $request->input('mot_de_passe', ''));

        if (empty($emailOuTel) || empty($motDePasse)) {
            return response()->json([
                'message' => 'Email/téléphone et mot de passe requis.',
            ], 422);
        }

        // ⭐ Normalise le téléphone (retire +242 / 242 en début)
        $telNormalise = preg_replace('/^\+?242/', '', $emailOuTel);
        $telAvecZero  = '0' . ltrim($telNormalise, '0');

        // ⭐ Cherche par email OU par plusieurs formats de téléphone
        $user = CanadianUser::where('email', $emailOuTel)
            ->orWhere('telephone', $emailOuTel)
            ->orWhere('telephone', $telNormalise)
            ->orWhere('telephone', $telAvecZero)
            ->orWhere('telephone', '+242' . ltrim($telNormalise, '0'))
            ->first();

        if (!$user || !Hash::check($motDePasse, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Les identifiants fournis sont incorrects.'],
            ]);
        }

        // Supprime les anciens tokens
        $user->tokens()->delete();

        // Token Sanctum avec ability canadian-api
        $token = $user->createToken('canadian_token', ['canadian-api'])->plainTextToken;

        return response()->json([
            'message' => 'Connexion réussie',
            'user'    => $user,
            'token'   => $token,
        ]);
    }

    /**
     * ⭐ Réinitialisation du mot de passe
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // ⭐ REGEX : 9 chiffres, commence par 06, 04 ou 05
            'telephone'            => ['required', 'string', 'regex:/^(06|04|05)\d{7}$/', 'exists:canadian_users,telephone'],
            'jeton'                => 'required|string',
            'nouveau_mot_de_passe' => 'required|string|min:6',
        ], [
            'jeton.required'                => 'Vérifiez d\'abord votre numéro avec le code reçu.',
            'telephone.required'            => 'Le numéro de téléphone est obligatoire.',
            'telephone.regex'               => 'Le numéro doit commencer par 06, 04 ou 05 et contenir 9 chiffres.',
            'telephone.exists'              => 'Aucun compte trouvé avec ce numéro.',
            'nouveau_mot_de_passe.required' => 'Le nouveau mot de passe est obligatoire.',
            'nouveau_mot_de_passe.min'      => 'Le mot de passe doit contenir au moins 6 caractères.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        // Preuve que le numéro a reçu le code (vérification OTP « RESET_PASSWORD »)
        if (! JetonReinitialisation::consommer($request->telephone, $request->jeton)) {
            return response()->json([
                'message' => 'Votre vérification a expiré. Recommencez avec un nouveau code.',
            ], 403);
        }

        $user = CanadianUser::where('telephone', $request->telephone)->first();

        if (!$user) {
            return response()->json([
                'message' => 'Aucun compte trouvé avec ce numéro.',
            ], 404);
        }

        $user->password = Hash::make($request->nouveau_mot_de_passe);
        $user->save();

        // Invalide tous les tokens existants
        $user->tokens()->delete();

        return response()->json([
            'message' => 'Mot de passe réinitialisé avec succès.',
        ], 200);
    }

    /**
     * ⭐ Déconnexion
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Déconnexion réussie',
        ], 200);
    }
}