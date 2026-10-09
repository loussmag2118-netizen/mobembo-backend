<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\Trajet;
use App\Models\Bus;
use App\Models\VagueDepart;

class UpdatePlanificationVoyageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // <--- Indispensable pour autoriser la mise à jour
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $agenceId = Auth::user()->agence_id;

        return [
            'trajet_id' => [
                'required',
                'exists:trajets,id',
                function ($attribute, $value, $fail) use ($agenceId) {
                    $trajet = Trajet::find($value);
                    if (!$trajet || $trajet->agence_id !== $agenceId) {
                        $fail('Le trajet sélectionné est invalide.');
                    }
                },
            ],
            'bus_id' => [
                'required',
                'exists:bus,id',
                function ($attribute, $value, $fail) use ($agenceId) {
                    $bus = Bus::find($value);
                    if (!$bus || $bus->agence_id !== $agenceId) {
                        $fail('Le bus sélectionné est invalide.');
                    }
                },
            ],
            'vague_depart_id' => [
                'required',
                'exists:vague_departs,id',
                function ($attribute, $value, $fail) use ($agenceId) {
                    $vague = VagueDepart::find($value);
                    if (!$vague || $vague->agence_id !== $agenceId) {
                        $fail('La vague de départ sélectionnée est invalide.');
                    }
                },
            ],
            'date_voyage' => 'required|date',
            'statut_voyage' => 'required|in:Programmation,En cours,Terminé,Annulé',
        ];
    }
}
