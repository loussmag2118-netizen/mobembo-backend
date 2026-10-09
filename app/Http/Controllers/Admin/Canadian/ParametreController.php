<?php

namespace App\Http\Controllers\Admin\Canadian;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Parametre;

class ParametreController extends Controller
{
    public function index()
    {
        // Récupère tous les paramètres groupés ou sous forme de tableau associatif
        $parametres = Parametre::all()->pluck('valeur', 'cle');
        return view('admin.canadian.parametres.index', compact('parametres'));
    }

    public function update(Request $request)
    {
        // Parcourt tous les champs envoyés par le formulaire de l'admin
        foreach ($request->except('_token', '_method') as $cle => $valeur) {
            Parametre::updateOrCreate(
                ['cle' => $cle],
                ['valeur' => $valeur]
            );
        }

        return redirect()->route('canadian.parametres.index')
            ->with('success', 'Paramètres mis à jour avec succès.');
    }
}
