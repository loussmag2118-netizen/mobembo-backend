<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
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
                'envoye_aux_agents'  => true, // Déjà accessible au scan des agents de l'agence
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

        // Réincrémente la place sur la planification
        if ($ticket->planificationVoyage) {
            $ticket->planificationVoyage->increment('places_disponibles');
        }

        $ticket->delete();

        return redirect()
            ->route('admin.voyageurs.index')
            ->with('success', 'Le voyageur et son billet ont été supprimés avec succès.');
    }
}
