<?php

namespace App\Http\Controllers\Admin\Canadian;

use App\Models\Passager;
use Illuminate\Http\Request;

/**
 * Passagers des réservations de la compagnie (listes d'embarquement).
 */
class PassagerController extends EspaceAerienController
{
    public function index(Request $request)
    {
        $agenceId = $this->compagnieId();

        $passagers = Passager::with('reservation.vol')
            ->whereHas('reservation', fn ($r) => $r->where('agence_id', $agenceId)
                ->when($request->boolean('payes', true), fn ($q) => $q->whereIn('statut', ['Paye', 'Confirmee'])))
            ->when($request->filled('q'), function ($q) use ($request) {
                $t = trim($request->q);
                $q->where(fn ($w) => $w->where('nom', 'like', "%{$t}%")
                    ->orWhere('prenom', 'like', "%{$t}%")
                    ->orWhere('numero_passeport', 'like', "%{$t}%"));
            })
            ->when($request->filled('vol'), fn ($q) => $q->whereHas('reservation.vol', fn ($v) => $v->where('numero_vol', 'like', '%' . trim($request->vol) . '%')))
            ->when(in_array($request->type, ['adulte', 'enfant', 'bebe'], true), fn ($q) => $q->where('type', $request->type))
            ->latest()
            ->paginate(5)->withQueryString();

        return view('admin.canadian.passagers.index', compact('passagers'));
    }
}
