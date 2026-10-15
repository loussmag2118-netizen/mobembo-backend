<?php

namespace App\Http\Controllers;

use App\Models\CanadianUser;
use App\Models\Utilisateur;
use App\Support\SuppressionCompte;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * Pages publiques exigées par Google Play : politique de confidentialité
 * et suppression de compte depuis le web.
 */
class PagesPubliquesController extends Controller
{
    public function confidentialite()
    {
        return view('public.confidentialite');
    }

    public function suppressionCompte()
    {
        return view('public.suppression-compte');
    }

    public function supprimerCompte(Request $request)
    {
        $request->validate([
            'espace'       => 'required|in:bus,canadian',
            'telephone'    => ['required', 'string', 'regex:/^0[4-6]\d{7}$/'],
            'mot_de_passe' => 'required|string',
            'confirmation' => 'accepted',
        ], [
            'telephone.regex'       => 'Le numéro doit contenir 9 chiffres (ex. 061234567).',
            'confirmation.accepted' => 'Cochez la case pour confirmer la suppression.',
        ]);

        $refus = back()->withInput($request->only('espace', 'telephone'))
            ->withErrors(['telephone' => 'Numéro ou mot de passe incorrect.']);

        if ($request->espace === 'canadian') {
            $user = CanadianUser::where('telephone', $request->telephone)->first();
            if (! $user || ! Hash::check($request->mot_de_passe, $user->password)) {
                return $refus;
            }
            SuppressionCompte::canadien($user);
        } else {
            $user = Utilisateur::where('telephone', $request->telephone)->where('role', 'Client')->first();
            if (! $user || ! Hash::check($request->mot_de_passe, $user->mot_de_passe)) {
                return $refus;
            }
            SuppressionCompte::client($user);
        }

        return redirect()->route('compte.suppression')->with('supprime', true);
    }
}
