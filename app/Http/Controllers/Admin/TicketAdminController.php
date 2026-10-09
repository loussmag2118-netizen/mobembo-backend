<?php

namespace App\Http\Controllers\Admin;

use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;

class TicketAdminController extends Controller
{
    protected const STATUTS = ['En attente', 'Embarqué', 'Annulé'];

    /**
     * Récupérer la liste des voyageurs (filtrée par agence si nécessaire).
     */
    public function index(Request $request)
    {
        $query = Ticket::with(['planificationVoyage.trajet', 'utilisateur', 'bus']);

        if ($request->has('agence') && !empty($request->agence)) {
            $query->where('agence_depart', $request->agence);
        }

        $tickets = $query->latest()->get();

        return response()->json([
            'success' => true,
            'voyageurs' => $tickets,
        ], 200);
    }

    /**
     * Scanner un ticket.
     */
    public function scan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reference_ticket' => 'required|string|exists:tickets,reference_ticket',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket invalide ou introuvable.'
            ], 404);
        }

        $ticket = Ticket::with(['planificationVoyage.trajet', 'utilisateur', 'bus'])
            ->where('reference_ticket', $request->reference_ticket)
            ->first();

        return response()->json([
            'success' => true,
            'message' => 'Ticket valide.',
            'ticket' => $ticket,
        ], 200);
    }

    /**
     * Mettre à jour le statut d'embarquement.
     */
    public function updateEmbarquement(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reference_ticket' => 'required|string|exists:tickets,reference_ticket',
            'statut_embarquement' => ['required', Rule::in(self::STATUTS)],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $ticket = Ticket::where('reference_ticket', $request->reference_ticket)->first();

        if ($ticket->statut_embarquement === 'Embarqué' && $request->statut_embarquement === 'Embarqué') {
            return response()->json([
                'success' => false,
                'message' => 'Ce passager est déjà enregistré comme embarqué.'
            ], 400);
        }

        $ticket->update([
            'statut_embarquement' => $request->statut_embarquement,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Embarquement confirmé.',
            'ticket' => $ticket->load(['planificationVoyage.trajet', 'utilisateur', 'bus']),
        ], 200);
    }
}
