<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\CanadianUser;
use App\Models\Reservation;
use App\Models\Vol;
use App\Support\Audit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * Partenaire aérien (Canadian Airways) : activité et comptes
 * administrateurs de son espace web /canadian/admin.
 */
class AerienController extends Controller
{
    public function index()
    {
        $admins = CanadianUser::where('est_admin', true)->orderBy('id')->get();

        $stats = [
            'vols'         => Vol::count(),
            'vols_a_venir' => Vol::where('date_depart', '>=', now())->count(),
            'reservations' => Reservation::count(),
            'clients'      => CanadianUser::where('est_admin', false)->count(),
        ];

        return view('superadmin.aerien.index', compact('admins', 'stats'));
    }

    public function ajouterAdmin(Request $request)
    {
        $request->validate([
            'nom'       => 'required|string|max:100',
            'prenom'    => 'required|string|max:100',
            'email'     => 'required|email|max:255|unique:canadian_users,email',
            'telephone' => ['required', 'string', 'regex:/^(05|06)\d{7}$/', 'unique:canadian_users,telephone'],
        ], [
            'email.unique'     => 'Cet email est déjà utilisé par un compte Canadian.',
            'telephone.unique' => 'Ce numéro est déjà utilisé par un compte Canadian (client ou admin).',
            'telephone.regex'  => 'Le numéro doit commencer par 05 ou 06 et avoir 9 chiffres.',
        ]);

        $motDePasse = AgenceController::motDePasseTemporaire();
        $admin = new CanadianUser([
            'name'            => trim($request->prenom . ' ' . $request->nom),
            'nom'             => $request->nom,
            'prenom'          => $request->prenom,
            'email'           => $request->email,
            'telephone'       => $request->telephone,
            'password'        => Hash::make($motDePasse),
            'compte_confirme' => true,
        ]);
        $admin->est_admin = true;
        $admin->save();
        Audit::log('superadmin.aerien.admin_ajoute', $admin, ['email' => $admin->email]);

        return back()
            ->with('success', "Administrateur {$admin->prenom} {$admin->nom} ajouté.")
            ->with('mot_de_passe_temporaire', ['compte' => "{$admin->prenom} {$admin->nom} ({$admin->email})", 'mot_de_passe' => $motDePasse]);
    }

    public function reinitialiserAdmin(CanadianUser $compte)
    {
        abort_unless($compte->est_admin, 404);

        $motDePasse = AgenceController::motDePasseTemporaire();
        $compte->update(['password' => Hash::make($motDePasse)]);
        $compte->tokens()->delete();
        Audit::log('superadmin.aerien.mot_de_passe_reinitialise', $compte, ['email' => $compte->email]);

        return back()->with('mot_de_passe_temporaire', [
            'compte'       => "{$compte->name} ({$compte->email})",
            'mot_de_passe' => $motDePasse,
        ]);
    }

    public function retirerAdmin(CanadianUser $compte)
    {
        abort_unless($compte->est_admin, 404);

        if (CanadianUser::where('est_admin', true)->count() <= 1) {
            return back()->withErrors(['admin' => 'Impossible de retirer le dernier administrateur : ajoutez-en un autre d\'abord.']);
        }

        Audit::log('superadmin.aerien.admin_retire', $compte, ['email' => $compte->email]);
        $compte->tokens()->delete();
        $compte->delete();

        return back()->with('success', "L'accès de {$compte->name} a été retiré.");
    }
}
