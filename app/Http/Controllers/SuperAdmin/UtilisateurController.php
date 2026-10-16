<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\CanadianUser;
use App\Models\Utilisateur;
use Illuminate\Http\Request;

/**
 * Clients inscrits sur les applications mobiles (consultation seule) :
 * transport par bus (table utilisateurs, rôle Client) et aérien
 * (comptes Canadian Airways qui ne sont pas administrateurs).
 */
class UtilisateurController extends Controller
{
    public function index(Request $request)
    {
        $espace = $request->query('espace') === 'aerien' ? 'aerien' : 'bus';
        $q      = trim((string) $request->query('q', ''));
        $statut = in_array($request->query('statut'), ['confirme', 'non_confirme'], true) ? $request->query('statut') : null;

        $requete = $espace === 'bus'
            ? Utilisateur::where('role', 'Client')->withCount(['tickets as achats_count' => fn ($t) => $t->where('statut_paiement', 'Paye')])
            : CanadianUser::where('est_admin', false)->withCount(['reservations as achats_count']);

        if ($q !== '') {
            $requete->where(function ($w) use ($q, $espace) {
                $w->where('telephone', 'like', "%{$q}%")
                  ->orWhere('nom', 'like', "%{$q}%")
                  ->orWhere('prenom', 'like', "%{$q}%")
                  ->orWhere('email', 'like', "%{$q}%");
                if ($espace === 'aerien') {
                    $w->orWhere('name', 'like', "%{$q}%");
                }
            });
        }
        if ($statut) {
            $requete->where('compte_confirme', $statut === 'confirme');
        }

        $utilisateurs = $requete->latest('id')->paginate(5)->withQueryString();

        // Chiffres de tout l'espace (pas seulement de la page)
        $base = fn () => $espace === 'bus'
            ? Utilisateur::where('role', 'Client')
            : CanadianUser::where('est_admin', false);

        $stats = [
            'total'        => $base()->count(),
            'confirmes'    => $base()->where('compte_confirme', true)->count(),
            'aujourdhui'   => $base()->whereDate('created_at', today())->count(),
            'semaine'      => $base()->where('created_at', '>=', now()->subDays(7))->count(),
        ];
        $totaux = [
            'bus'    => Utilisateur::where('role', 'Client')->count(),
            'aerien' => CanadianUser::where('est_admin', false)->count(),
        ];

        return view('superadmin.utilisateurs.index', compact('utilisateurs', 'espace', 'q', 'statut', 'stats', 'totaux'));
    }

    /** Réseau d'après le préfixe congolais : 05 Airtel, 06 MTN (04 : inconnu). */
    public static function reseau(?string $telephone): ?string
    {
        $chiffres = preg_replace('/\D/', '', (string) $telephone);
        $chiffres = preg_replace('/^242/', '', $chiffres);
        $chiffres = str_starts_with($chiffres, '0') ? $chiffres : '0' . $chiffres;

        return match (substr($chiffres, 0, 2)) {
            '06'    => 'MTN',
            '05'    => 'Airtel',
            default => null,
        };
    }
}
