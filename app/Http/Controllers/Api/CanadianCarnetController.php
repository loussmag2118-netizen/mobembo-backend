<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CarnetPassager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CanadianCarnetController extends Controller
{
    public function index(Request $request)
    {
        $passagers = CarnetPassager::where('canadian_user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'success'   => true,
            'passagers' => $passagers,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nom'               => 'required|string|max:150',
            'prenom'            => 'required|string|max:150',
            'date_naissance'    => 'nullable|date',
            'numero_passeport'  => 'nullable|string|max:50',
            'nationalite'       => 'nullable|string|max:100',
            'type'              => 'nullable|in:adulte,enfant,bebe',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $passager = CarnetPassager::updateOrCreate(
            [
                'canadian_user_id' => $request->user()->id,
                'nom'              => $request->nom,
                'prenom'           => $request->prenom,
                'date_naissance'   => $request->date_naissance,
            ],
            [
                'numero_passeport' => $request->numero_passeport,
                'nationalite'      => $request->nationalite ?? 'Congo',
                'type'             => $request->type ?? 'adulte',
            ]
        );

        return response()->json([
            'success'  => true,
            'message'  => 'Passager enregistré.',
            'passager' => $passager,
        ], 201);
    }

    public function show(Request $request, $id)
    {
        $passager = CarnetPassager::where('canadian_user_id', $request->user()->id)
            ->findOrFail($id);

        return response()->json([
            'success'  => true,
            'passager' => $passager,
        ]);
    }

    public function update(Request $request, $id)
    {
        $passager = CarnetPassager::where('canadian_user_id', $request->user()->id)
            ->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nom'              => 'nullable|string|max:150',
            'prenom'           => 'nullable|string|max:150',
            'date_naissance'   => 'nullable|date',
            'numero_passeport' => 'nullable|string|max:50',
            'nationalite'      => 'nullable|string|max:100',
            'type'             => 'nullable|in:adulte,enfant,bebe',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $passager->update($validator->validated());

        return response()->json([
            'success'  => true,
            'message'  => 'Passager mis à jour.',
            'passager' => $passager,
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $passager = CarnetPassager::where('canadian_user_id', $request->user()->id)
            ->findOrFail($id);
        $passager->delete();

        return response()->json([
            'success' => true,
            'message' => 'Passager supprimé du carnet.',
        ]);
    }
}