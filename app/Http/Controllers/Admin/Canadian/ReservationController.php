<?php

namespace App\Http\Controllers\Admin\Canadian;

use App\Models\Notification;
use App\Models\Reservation;
use App\Services\ReservationVolService;
use App\Support\Audit;
use Illuminate\Http\Request;

class ReservationController extends EspaceAerienController
{
    public const STATUTS = [
        'En_Attente'   => 'En attente de paiement',
        'Paye'         => 'Payées',
        'Confirmee'    => 'Embarquement confirmé',
        'A_Rembourser' => 'À rembourser',
        'Annulee'      => 'Annulées',
        'Remboursee'   => 'Remboursées',
        'Expiree'      => 'Expirées',
        'Echouee'      => 'Paiement échoué',
    ];

    public function __construct(private ReservationVolService $reservations)
    {
    }

    public function index(Request $request)
    {
        $this->reservations->expirer();
        $agenceId = $this->compagnieId();

        $reservations = Reservation::deLaCompagnie($agenceId)
            ->with(['vol', 'canadianUser'])
            ->when($request->filled('q'), function ($q) use ($request) {
                $t = trim($request->q);
                $q->where(fn ($w) => $w->where('reference', 'like', "%{$t}%")
                    ->orWhere('code_pnr', 'like', "%{$t}%")
                    ->orWhere('nom_passager', 'like', "%{$t}%")
                    ->orWhere('telephone', 'like', "%{$t}%")
                    ->orWhereHas('vol', fn ($v) => $v->where('numero_vol', 'like', "%{$t}%")));
            })
            ->when(array_key_exists((string) $request->statut, self::STATUTS), fn ($q) => $q->where('statut', $request->statut))
            ->latest()
            ->paginate(5)->withQueryString();

        $compteurs = Reservation::deLaCompagnie($agenceId)->selectRaw('statut, COUNT(*) AS n')->groupBy('statut')->pluck('n', 'statut');

        return view('admin.canadian.reservations.index', compact('reservations', 'compteurs'));
    }

    public function show($id)
    {
        $reservation = $this->reservation($id)->load(['vol', 'canadianUser', 'passagers', 'reservationExtras.extra', 'paiement']);

        return view('admin.canadian.reservations.show', compact('reservation'));
    }

    /** Annulation par la compagnie : places rendues ; une réservation payée passe « à rembourser ». */
    public function annuler($id)
    {
        $reservation = $this->reservation($id);
        if (! $reservation->occupeDesPlaces()) {
            return back()->with('error', 'Cette réservation n\'est plus active.');
        }

        $etaitPayee = in_array($reservation->statut, ['Paye', 'Confirmee'], true);
        $this->reservations->liberer($reservation, $etaitPayee ? 'A_Rembourser' : 'Annulee');
        $this->notifier($reservation, 'Réservation annulée', 'Votre réservation ' . $reservation->reference . ' a été annulée par la compagnie.'
            . ($etaitPayee ? ' Vous allez être remboursé.' : ''), 'warning');
        Audit::log('vol.reservation_annulee', $reservation, ['payee' => $etaitPayee], $this->compagnieId());

        return back()->with('success', $etaitPayee
            ? 'Réservation annulée. Elle est maintenant « à rembourser » : effectuez le remboursement puis confirmez-le.'
            : 'Réservation annulée, les places ont été rendues.');
    }

    /** Le remboursement a été effectué (hors plateforme) : on le note. */
    public function rembourser($id)
    {
        $reservation = $this->reservation($id);
        if ($reservation->statut !== 'A_Rembourser') {
            return back()->with('error', 'Seule une réservation « à rembourser » peut être marquée remboursée.');
        }

        $reservation->update(['statut' => 'Remboursee']);
        $this->notifier($reservation, 'Remboursement effectué', 'Le remboursement de votre réservation ' . $reservation->reference . ' a été effectué.', 'success');
        Audit::log('vol.reservation_remboursee', $reservation, ['montant' => $reservation->montant_total], $this->compagnieId());

        return back()->with('success', 'Remboursement enregistré.');
    }

    /** Envoie à nouveau le billet (PNR) dans les notifications du client. */
    public function renvoyerBillet($id)
    {
        $reservation = $this->reservation($id)->load('vol');
        if (! in_array($reservation->statut, ['Paye', 'Confirmee'], true)) {
            return back()->with('error', 'Le billet n\'existe qu\'une fois la réservation payée.');
        }

        $vol = $reservation->vol;
        $this->notifier($reservation, 'Votre billet',
            "Vol {$vol?->numero_vol} {$vol?->ville_depart} → {$vol?->ville_arrivee} le " . $vol?->date_depart?->format('d/m/Y à H:i')
            . ". Code de réservation (PNR) : {$reservation->code_pnr}.", 'info');

        return back()->with('success', 'Le billet a été renvoyé dans les notifications du client.');
    }

    /** Seul changement manuel autorisé : confirmer l'embarquement d'une réservation payée. */
    public function modifierStatut(Request $request, $id)
    {
        $request->validate(['statut' => 'required|in:Confirmee']);
        $reservation = $this->reservation($id);
        if ($reservation->statut !== 'Paye') {
            return back()->with('error', 'Seule une réservation payée peut être confirmée à l\'embarquement.');
        }

        $reservation->update(['statut' => 'Confirmee']);
        Audit::log('vol.embarquement_confirme', $reservation, [], $this->compagnieId());

        return back()->with('success', 'Embarquement confirmé.');
    }

    private function reservation($id): Reservation
    {
        return Reservation::deLaCompagnie($this->compagnieId())->findOrFail($id);
    }

    private function notifier(Reservation $reservation, string $titre, string $message, string $type): void
    {
        if (! $reservation->canadian_user_id) {
            return;
        }
        Notification::create([
            'agence_id'        => $this->compagnieId(),
            'canadian_user_id' => $reservation->canadian_user_id,
            'titre'            => $titre,
            'message'          => $message,
            'type'             => $type,
            'lu'               => false,
        ]);
    }
}
