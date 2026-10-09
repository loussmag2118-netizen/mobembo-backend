<?php

namespace App\Http\Controllers\Admin\Canadian;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    public function index()
    {
        $reservations = Reservation::with(['vol', 'canadianUser'])
            ->latest()
            ->paginate(15);

        $total      = Reservation::count();
        $confirmees = Reservation::where('statut', 'Confirmee')->count();
        $en_attente = Reservation::where('statut', 'En_Attente')->count();
        $annulees   = Reservation::where('statut', 'Annulee')->count();

        return view('admin.canadian.reservations.index', compact(
            'reservations', 'total', 'confirmees', 'en_attente', 'annulees'
        ));
    }

    public function show($id)
    {
        $reservation = Reservation::with([
            'vol',
            'volRetour',
            'canadianUser',
            'passagers',
            'reservationExtras.extra',
        ])->findOrFail($id);

        return view('admin.canadian.reservations.show', compact('reservation'));
    }

    public function annuler($id)
    {
        $reservation = Reservation::findOrFail($id);

        // Remettre les places dans le vol
        if ($reservation->vol) {
            $reservation->vol->increment('places_disponibles', $reservation->nombre_places);
        }

        $reservation->update(['statut' => 'Annulee']);

        return redirect()->back()
            ->with('success', 'Réservation annulée avec succès.');
    }

    public function rembourser($id)
    {
        $reservation = Reservation::findOrFail($id);
        $reservation->update(['statut' => 'Remboursee']);

        return redirect()->back()
            ->with('success', 'Réservation remboursée avec succès.');
    }

    public function renvoyerBillet($id)
    {
        $reservation = Reservation::findOrFail($id);

        // TODO : logique d'envoi de mail ou SMS au client

        return redirect()->back()
            ->with('success', 'Le billet a été renvoyé au client avec succès.');
    }

    public function modifierStatut(Request $request, $id)
    {
        $validated = $request->validate([
            'statut' => 'required|string|in:En_Attente,Confirmee,Annulee,Remboursee',
        ]);

        $reservation = Reservation::findOrFail($id);
        $reservation->update($validated);

        return redirect()->back()
            ->with('success', 'Statut de la réservation mis à jour.');
    }
}