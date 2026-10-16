<?php

namespace App\Http\Controllers\Admin\Canadian;

use App\Models\Parametre;
use Illuminate\Http\Request;

/**
 * Coordonnées et règles commerciales de la compagnie.
 */
class ParametreController extends EspaceAerienController
{
    /** clé => [libellé, règle de validation, valeur par défaut] */
    public const CHAMPS = [
        'email_contact'      => ['Email du service client', 'nullable|email|max:150', ''],
        'telephone'          => ['Téléphone du service client', 'nullable|string|max:30', ''],
        'adresse'            => ['Adresse de l\'agence principale', 'nullable|string|max:200', ''],
        'franchise_bagage'   => ['Bagage inclus dans le billet', 'nullable|string|max:100', '1 bagage cabine de 7 kg'],
        'enregistrement_min' => ['Fin de l\'enregistrement (minutes avant le départ)', 'nullable|integer|min:15|max:240', '60'],
        'conditions_annulation' => ['Conditions d\'annulation et de remboursement', 'nullable|string|max:1000', ''],
    ];

    public function index()
    {
        $valeurs = Parametre::deLaCompagnie($this->compagnieId())->pluck('valeur', 'cle');
        $parametres = collect(self::CHAMPS)->mapWithKeys(fn ($c, $cle) => [$cle => $valeurs[$cle] ?? $c[2]]);

        return view('admin.canadian.parametres.index', compact('parametres'));
    }

    public function update(Request $request)
    {
        $regles = collect(self::CHAMPS)->map(fn ($c) => $c[1])->all();
        $donnees = $request->validate($regles);

        foreach (array_keys(self::CHAMPS) as $cle) {
            Parametre::updateOrCreate(
                ['agence_id' => $this->compagnieId(), 'cle' => $cle],
                ['valeur' => $donnees[$cle] ?? null]
            );
        }

        return redirect()->route('canadian.parametres.index')->with('success', 'Paramètres enregistrés.');
    }
}
