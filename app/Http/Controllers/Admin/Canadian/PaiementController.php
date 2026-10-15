<?php

namespace App\Http\Controllers\Admin\Canadian;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Paiement;

class PaiementController extends Controller
{
    /**
     * Liste des paiements et transactions.
     */
    public function index()
    {
        $paiements = Paiement::latest()->paginate(5)->withQueryString();

        // Statistiques
        $revenus    = Paiement::where('statut', 'Reussi')->sum('montant');
        $reussies   = Paiement::where('statut', 'Reussi')->count();
        $en_attente = Paiement::where('statut', 'En_Attente')->count();

        return view('admin.canadian.paiements.index', compact(
            'paiements',
            'revenus',
            'reussies',
            'en_attente'
        ));
    }

    /**
     * Détails d'un paiement.
     */
    public function show($id)
    {
        $paiement = Paiement::findOrFail($id);

        return view('admin.canadian.paiements.show', compact('paiement'));
    }

    /**
     * Export CSV des paiements.
     */
    public function export()
    {
        $filename = 'paiements_canadian_' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');

            // En-têtes CSV
            fputcsv($file, [
                'ID',
                'Référence',
                'Client',
                'Téléphone',
                'Méthode',
                'Montant (FCFA)',
                'Statut',
                'Date',
            ]);

            // Données
            Paiement::orderByDesc('created_at')->chunk(500, function ($paiements) use ($file) {
                foreach ($paiements as $p) {
                    fputcsv($file, [
                        $p->id,
                        $p->reference_transaction ?? '—',
                        $p->nom_client ?? '—',
                        $p->telephone_client ?? '—',
                        $p->methode ?? '—',
                        $p->montant ?? 0,
                        $p->statut ?? '—',
                        $p->created_at?->format('d/m/Y H:i') ?? '—',
                    ]);
                }
            });

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}