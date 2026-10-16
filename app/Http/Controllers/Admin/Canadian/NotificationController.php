<?php

namespace App\Http\Controllers\Admin\Canadian;

use App\Models\CanadianUser;
use App\Models\Notification;
use App\Models\Reservation;
use App\Support\Audit;
use Illuminate\Http\Request;

/**
 * Messages envoyés aux clients de la compagnie (ceux qui ont réservé chez
 * elle), visibles dans l'onglet Notifications de l'application.
 */
class NotificationController extends EspaceAerienController
{
    public function index()
    {
        $historique = Notification::deLaCompagnie($this->compagnieId())
            ->with('canadianUser')
            ->latest()
            ->paginate(5)->withQueryString();
        $clients = $this->clients()->orderBy('nom')->get();

        return view('admin.canadian.notifications.index', compact('historique', 'clients'));
    }

    public function store(Request $request)
    {
        $v = $request->validate([
            'titre'      => 'required|string|max:120',
            'message'    => 'required|string|max:1000',
            'type'       => 'required|in:info,promo,warning,success',
            'cible'      => 'required|in:tous,vol,client',
            'numero_vol' => 'required_if:cible,vol|nullable|string|max:20',
            'client_id'  => 'required_if:cible,client|nullable|integer',
        ], [
            'titre.required'         => 'Le titre est obligatoire.',
            'message.required'       => 'Le message est obligatoire.',
            'numero_vol.required_if' => 'Indiquez le numéro du vol.',
            'client_id.required_if'  => 'Choisissez le client.',
        ]);

        $destinataires = match ($v['cible']) {
            'client' => $this->clients()->whereKey($v['client_id'])->pluck('id'),
            'vol'    => Reservation::deLaCompagnie($this->compagnieId())
                ->whereIn('statut', ['Paye', 'Confirmee'])
                ->whereHas('vol', fn ($q) => $q->where('numero_vol', strtoupper(trim($v['numero_vol']))))
                ->pluck('canadian_user_id')->filter()->unique(),
            default  => $this->clients()->pluck('id'),
        };

        if ($destinataires->isEmpty()) {
            return back()->withInput()->with('error', 'Aucun client ne correspond : rien n\'a été envoyé.');
        }

        foreach ($destinataires as $clientId) {
            Notification::create([
                'agence_id'        => $this->compagnieId(),
                'canadian_user_id' => $clientId,
                'titre'            => $v['titre'],
                'message'          => $v['message'],
                'type'             => $v['type'],
                'lu'               => false,
            ]);
        }
        Audit::log('vol.notification_envoyee', null, ['cible' => $v['cible'], 'destinataires' => $destinataires->count()], $this->compagnieId());

        return redirect()->route('canadian.notifications.index')
            ->with('success', 'Message envoyé à ' . $destinataires->count() . ' client(s).');
    }

    public function destroy($id)
    {
        Notification::deLaCompagnie($this->compagnieId())->findOrFail($id)->delete();

        return back()->with('success', 'Notification supprimée.');
    }

    /** Clients ayant au moins une réservation dans la compagnie. */
    private function clients()
    {
        $agenceId = $this->compagnieId();

        return CanadianUser::whereHas('reservations', fn ($r) => $r->where('agence_id', $agenceId));
    }
}
