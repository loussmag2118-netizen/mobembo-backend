<?php

namespace App\Http\Controllers\Admin\Canadian;

use App\Models\Paiement;
use Illuminate\Http\Request;

/**
 * Encaissements confirmés par MTN / Airtel pour les réservations de la compagnie.
 */
class PaiementController extends EspaceAerienController
{
    public function index(Request $request)
    {
        $paiements = $this->filtrer($request)->with('reservation.vol')->latest()->paginate(5)->withQueryString();

        $base = Paiement::deLaCompagnie($this->compagnieId())->where('statut', 'Reussi');
        $stats = [
            'total'   => (float) (clone $base)->sum('montant'),
            'mois'    => (float) (clone $base)->where('created_at', '>=', now()->startOfMonth())->sum('montant'),
            'jour'    => (float) (clone $base)->whereDate('created_at', today())->sum('montant'),
            'nombre'  => (clone $base)->count(),
        ];
        $parMethode = (clone $base)->selectRaw('methode, SUM(montant) AS total')->groupBy('methode')->pluck('total', 'methode');

        return view('admin.canadian.paiements.index', compact('paiements', 'stats', 'parMethode'));
    }

    public function export(Request $request)
    {
        $nom = 'paiements_' . \Illuminate\Support\Str::slug($this->compagnie()->nom_agence) . '_' . date('Y-m-d') . '.csv';
        $requete = $this->filtrer($request)->with('reservation.vol')->orderByDesc('created_at');

        return response()->streamDownload(function () use ($requete) {
            $f = fopen('php://output', 'w');
            fwrite($f, "\xEF\xBB\xBF");   // accents lisibles dans Excel
            fputcsv($f, ['Date', 'Transaction', 'Réservation', 'Vol', 'Client', 'Téléphone', 'Moyen', 'Montant (FCFA)', 'Statut'], ';');
            $requete->chunk(500, function ($paiements) use ($f) {
                foreach ($paiements as $p) {
                    fputcsv($f, [
                        $p->created_at?->format('d/m/Y H:i'), $p->reference_transaction, $p->reservation?->reference,
                        $p->reservation?->vol?->numero_vol, $p->nom_client, $p->telephone_client, $p->methode,
                        (int) $p->montant, $p->statut,
                    ], ';');
                }
            });
            fclose($f);
        }, $nom, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function filtrer(Request $request)
    {
        return Paiement::deLaCompagnie($this->compagnieId())
            ->when($request->filled('q'), function ($q) use ($request) {
                $t = trim($request->q);
                $q->where(fn ($w) => $w->where('reference_transaction', 'like', "%{$t}%")
                    ->orWhere('nom_client', 'like', "%{$t}%")
                    ->orWhere('telephone_client', 'like', "%{$t}%"));
            })
            ->when(in_array($request->methode, ['MTN', 'Airtel'], true), fn ($q) => $q->where('methode', $request->methode))
            ->when($request->filled('du'), fn ($q) => $q->whereDate('created_at', '>=', $request->du))
            ->when($request->filled('au'), fn ($q) => $q->whereDate('created_at', '<=', $request->au));
    }
}
