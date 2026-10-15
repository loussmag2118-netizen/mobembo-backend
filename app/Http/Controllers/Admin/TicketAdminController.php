<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * Consultation et contrôle des billets par le personnel d'une agence.
 * Toutes les actions sont limitées aux agents / admins de l'agence du billet.
 */
class TicketAdminController extends Controller
{
    protected const STATUTS = ['En_Attente', 'Present', 'Absent'];

    /**
     * Billets de l'agence de l'utilisateur connecté.
     */
    public function index(Request $request)
    {
        $agent = $this->personnel();
        if (!$agent) {
            return response()->json(['success' => false, 'message' => 'Accès refusé.'], 403);
        }

        $tickets = Ticket::with(['planificationVoyage.trajet'])
            ->where('agence_id', $agent->agence_id)
            ->latest()
            ->limit(500)
            ->get();

        return response()->json(['success' => true, 'voyageurs' => $tickets], 200);
    }

    /**
     * Retrouver un billet de son agence par sa référence.
     */
    public function scan(Request $request)
    {
        $agent = $this->personnel();
        if (!$agent) {
            return response()->json(['success' => false, 'message' => 'Accès refusé.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'reference_ticket' => 'required|string|max:120',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $ticket = $this->billetDeLAgence($request->reference_ticket, $agent->agence_id);
        if (!$ticket) {
            return response()->json(['success' => false, 'message' => 'Ticket invalide ou introuvable.'], 404);
        }

        return response()->json([
            'success' => true,
            'message' => $ticket->statut_paiement === 'Paye' ? 'Ticket valide.' : 'Ticket non payé.',
            'ticket'  => $ticket->load('planificationVoyage.trajet'),
        ], 200);
    }

    /**
     * Mettre à jour le statut d'embarquement (Present / Absent / En_Attente).
     */
    public function updateEmbarquement(Request $request)
    {
        $agent = $this->personnel();
        if (!$agent) {
            return response()->json(['success' => false, 'message' => 'Accès refusé.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'reference_ticket'    => 'required|string|max:120',
            'statut_embarquement' => ['required', Rule::in(self::STATUTS)],
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $ticket = $this->billetDeLAgence($request->reference_ticket, $agent->agence_id);
        if (!$ticket) {
            return response()->json(['success' => false, 'message' => 'Ticket invalide ou introuvable.'], 404);
        }

        if ($request->statut_embarquement === 'Present' && $ticket->statut_paiement !== 'Paye') {
            return response()->json(['success' => false, 'message' => 'Billet non payé : embarquement refusé.'], 409);
        }

        if ($ticket->statut_embarquement === 'Present' && $request->statut_embarquement === 'Present') {
            return response()->json(['success' => false, 'message' => 'Ce passager est déjà enregistré comme embarqué.'], 400);
        }

        $ticket->update([
            'statut_embarquement' => $request->statut_embarquement,
            'agent_controleur_id' => $agent->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Embarquement mis à jour.',
            'ticket'  => $ticket->load('planificationVoyage.trajet'),
        ], 200);
    }

    /** Agent ou admin rattaché à une agence ; sinon null. */
    private function personnel()
    {
        $user = Auth::user();

        return ($user && $user->agence_id && in_array($user->role, ['Agent', 'Admin'], true)) ? $user : null;
    }

    /** Billet de l'agence, par référence ou par jeton de QR (« MBL:… » ou URL du billet). */
    private function billetDeLAgence(string $code, int $agenceId): ?Ticket
    {
        $code = trim($code);
        $jeton   = Ticket::jetonDepuisCode($code);
        $colonne = $jeton !== null ? 'qr_code_token' : 'reference_ticket';
        $valeur  = $jeton ?? $code;

        return Ticket::where($colonne, $valeur)->where('agence_id', $agenceId)->first();
    }
}
