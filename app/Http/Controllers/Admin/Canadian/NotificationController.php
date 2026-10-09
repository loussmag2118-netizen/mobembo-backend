<?php

namespace App\Http\Controllers\Admin\Canadian;

use App\Http\Controllers\Controller;
use App\Models\CanadianUser;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Liste + formulaire d'envoi (tout sur une seule page).
     */
    public function index()
    {
        $historique = Notification::latest()->take(20)->get();
        $clients    = CanadianUser::orderBy('nom')->get();

        return view('admin.canadian.notifications.index', compact('historique', 'clients'));
    }

    /**
     * Enregistrer une notification Canadian.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'titre'     => 'required|string|max:255',
            'message'   => 'required|string',
            'client_id' => 'nullable|exists:canadian_users,id',
            'type'      => 'required|in:info,warning,success,promo',
        ], [
            'titre.required'     => 'Le titre est obligatoire.',
            'titre.max'          => 'Le titre ne peut pas dépasser 255 caractères.',
            'message.required'   => 'Le message est obligatoire.',
            'client_id.exists'   => 'Le client sélectionné n\'existe pas.',
            'type.required'      => 'Le type de notification est obligatoire.',
            'type.in'            => 'Le type doit être : info, warning, success ou promo.',
        ]);

        // ⭐ Mapping : client_id → canadian_user_id
        Notification::create([
            'canadian_user_id' => $validated['client_id'] ?? null,
            'utilisateur_id'   => null,
            'titre'            => $validated['titre'],
            'message'          => $validated['message'],
            'type'             => $validated['type'],
            'lu'               => false,
        ]);

        return redirect()
            ->route('canadian.notifications.index')
            ->with('success', 'Notification envoyée avec succès.');
    }

    /**
     * Suppression.
     */
    public function destroy($id)
    {
        $notification = Notification::findOrFail($id);
        $notification->delete();

        return redirect()
            ->route('canadian.notifications.index')
            ->with('success', 'Notification supprimée.');
    }
}