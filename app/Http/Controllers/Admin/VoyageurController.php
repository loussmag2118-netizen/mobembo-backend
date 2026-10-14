<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlanificationVoyage;
use App\Models\Ticket;
use App\Services\ReservationService;
use Illuminate\Support\Facades\DB;
use App\Support\Audit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VoyageurController extends Controller
{
    /**
     * Affiche la liste des voyageurs inscrits via l'application mobile pour l'agence connectée.
     */
    public function index()
    {
        $admin = Auth::user();
        $agenceId = $admin->agence_id;
        $agenceAdmin = $admin->agence?->nom_agence ?? 'Mon Agence';

        $tickets = Ticket::with([
                'planificationVoyage.trajet.gareDepart',
                'planificationVoyage.trajet.gareArrivee',
                'planificationVoyage.bus',
                'gareArrivee',
            ])
            ->where('agence_id', $agenceId)
            ->latest()
            ->get();

        // Transformation pour alimenter la vue Blade voyageurs
        $voyageurs = $tickets->map(function ($ticket) {
            $voyage      = $ticket->planificationVoyage;
            $trajet      = $voyage?->trajet;
            $gareDepart  = $trajet?->gareDepart?->nom_gare ?? '';
            $gareArrivee = $trajet?->gareArrivee?->nom_gare ?? '';

            $nomTrajet = $ticket->nom_trajet ?: ($gareDepart && $gareArrivee ? "$gareDepart → $gareArrivee" : 'Trajet réservé');
            $dateVoyage = $voyage?->date_voyage ? $voyage->date_voyage->format('d/m/Y') : ($ticket->created_at ? $ticket->created_at->format('d/m/Y') : 'N/A');

            return (object) [
                'id'                 => $ticket->id,
                'nom'                => $ticket->nom_passager,
                'prenom'             => $ticket->prenom_passager,
                'telephone'          => $ticket->telephone_passager,
                'reference'          => $ticket->reference_ticket,
                'nom_trajet'         => $nomTrajet,
                'date_voyage'        => $dateVoyage,
                'statut_embarquement'=> $ticket->statut_embarquement,
                'statut_paiement'    => $ticket->statut_paiement,
                'billet_test'        => \App\Services\PaiementService::estReferenceTest($ticket->reference_paiement),
            ];
        });

        return view('admin.voyageurs.index', compact('voyageurs', 'agenceAdmin'));
    }

    /**
     * Supprime ou annule un billet/voyageur.
     */
    public function destroy($id)
    {
        $agenceId = Auth::user()->agence_id;

        $ticket = Ticket::where('id', $id)
            ->where('agence_id', $agenceId)
            ->firstOrFail();

        // Supprime le billet puis recalcule les places du départ
        // (une place n'est rendue que si le billet en occupait une)
        DB::transaction(function () use ($ticket) {
            $voyage = $ticket->planification_voyage_id
                ? PlanificationVoyage::whereKey($ticket->planification_voyage_id)->lockForUpdate()->first()
                : null;

            $ticket->delete();

            if ($voyage) {
                app(ReservationService::class)->recalculerPlaces($voyage);
            }
        });

        return redirect()
            ->route('admin.voyageurs.index')
            ->with('success', 'Le voyageur et son billet ont été supprimés avec succès.');
    }

    /**
     * Marque un billet payé comme remboursé (après remboursement effectif
     * du client) : la place est libérée et l'opération est journalisée.
     */
    public function rembourser($id)
    {
        $agenceId = Auth::user()->agence_id;

        $ticket = Ticket::where('id', $id)
            ->where('agence_id', $agenceId)
            ->where('statut_paiement', 'Paye')
            ->firstOrFail();

        DB::transaction(function () use ($ticket) {
            $voyage = $ticket->planification_voyage_id
                ? PlanificationVoyage::whereKey($ticket->planification_voyage_id)->lockForUpdate()->first()
                : null;

            $ticket->update(['statut_paiement' => 'Rembourse']);

            if ($voyage) {
                app(ReservationService::class)->recalculerPlaces($voyage);
            }
        });

        Audit::log('billet.rembourse', $ticket, [
            'reference' => $ticket->reference_ticket,
            'montant'   => (float) $ticket->montant_total,
        ]);

        return redirect()
            ->route('admin.voyageurs.index')
            ->with('success', 'Billet marqué comme remboursé. La place a été libérée.');
    }
}
