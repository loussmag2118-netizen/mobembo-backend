<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Agence;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Vue nationale du super-administrateur : activité de toutes les agences
 * et journal d'audit.
 */
class TableauDeBordController extends Controller
{
    /** Billets réellement vendus (payés, hors billets de test). */
    private function billetsVendus()
    {
        return DB::table('tickets')
            ->where('statut_paiement', 'Paye')
            ->where(function ($q) {
                $q->whereNull('reference_paiement')
                  ->orWhere(fn ($q) => $q->where('reference_paiement', 'not like', 'SIM-%')
                                         ->where('reference_paiement', 'not like', 'AIRTEL-SIM-%'));
            });
    }

    public function index()
    {
        $parAgence = Agence::query()
            ->withCount([
                'utilisateurs as agents_count' => fn ($q) => $q->where('role', 'Agent'),
                'bus',
            ])
            ->orderBy('nom_agence')
            ->get()
            ->map(function (Agence $a) {
                // Compagnie aérienne : vols, réservations payées et chiffre d'affaires
                if ($a->estAerien()) {
                    $payees = DB::table('reservations')->where('agence_id', $a->id)
                        ->whereIn('statut', ['Paye', 'Confirmee'])
                        ->where(fn ($q) => $q->whereNull('reference_paiement')
                            ->orWhere(fn ($q) => $q->where('reference_paiement', 'not like', 'SIM-%')
                                                   ->where('reference_paiement', 'not like', 'AIRTEL-SIM-%')));

                    return (object) [
                        'agence'          => $a,
                        'agents'          => 0,
                        'bus'             => 0,
                        'departs_a_venir' => DB::table('vols')->where('agence_id', $a->id)->whereNull('deleted_at')
                            ->where('statut', 'programme')->where('date_depart', '>', now())->count(),
                        'billets'         => (int) (clone $payees)->sum('nombre_places'),
                        'chiffre'         => (float) (clone $payees)->sum('montant_total'),
                    ];
                }

                $ventes = $this->billetsVendus()->where('agence_id', $a->id);

                return (object) [
                    'agence'          => $a,
                    'agents'          => $a->agents_count,
                    'bus'             => $a->bus_count,
                    'departs_a_venir' => DB::table('planification_voyages')
                        ->where('agence_id', $a->id)->where('statut_voyage', 'Programmation')
                        ->whereDate('date_voyage', '>=', today())->count(),
                    'billets'         => (clone $ventes)->count(),
                    'chiffre'         => (float) (clone $ventes)->sum('montant_total'),
                ];
            });

        $stats = [
            'agences'         => $parAgence->count(),
            'agences_actives' => $parAgence->where('agence.statut', 'Actif')->count(),
            'agents'          => $parAgence->sum('agents'),
            'bus'             => $parAgence->sum('bus'),
            'departs_a_venir' => $parAgence->sum('departs_a_venir'),
            'billets'         => $parAgence->sum('billets'),
            'chiffre'         => $parAgence->sum('chiffre'),
            'billets_jour'    => (clone $this->billetsVendus())->whereDate('date_paiement', today())->count(),
            'billets_test'    => DB::table('tickets')->where('statut_paiement', 'Paye')
                ->where(fn ($q) => $q->where('reference_paiement', 'like', 'SIM-%')->orWhere('reference_paiement', 'like', 'AIRTEL-SIM-%'))
                ->count(),
            'a_rembourser'    => DB::table('audit_logs')->where('action', 'paiement.a_rembourser')->count(),
        ];

        $journal = DB::table('audit_logs')->orderByDesc('id')->limit(8)->get();
        $nomsAgences = Agence::pluck('nom_agence', 'id');

        return view('superadmin.dashboard', compact('parAgence', 'stats', 'journal', 'nomsAgences'));
    }

    /**
     * Journal d'audit de toute la plateforme, filtrable par agence et action.
     */
    public function journal(Request $request)
    {
        $requete = DB::table('audit_logs')->orderByDesc('id');

        if ($request->filled('agence')) {
            $requete->where('agence_id', (int) $request->agence);
        }
        if ($request->filled('action')) {
            $requete->where('action', 'like', '%' . str_replace(['%', '_'], ['\%', '\_'], $request->action) . '%');
        }
        if ($request->filled('du')) {
            $requete->whereDate('created_at', '>=', $request->du);
        }

        $entrees = $requete->paginate(5)->withQueryString();
        $nomsAgences = Agence::orderBy('nom_agence')->pluck('nom_agence', 'id');

        return view('superadmin.journal', compact('entrees', 'nomsAgences'));
    }
}
