<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Support\Audit;
use App\Support\Couleurs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Vitrine de l'agence (page d'accueil de l'agence dans l'application) :
 * logo, photos, slogan et couleur. Réservé à l'admin de l'agence.
 */
class VitrineController extends Controller
{
    public const PHOTOS_MAX = 6;

    private function agence()
    {
        $agence = Auth::user()->agence;
        abort_unless($agence, 403);

        return $agence;
    }

    public function edit()
    {
        $agence = $this->agence()->load(['logoMedia', 'photos']);

        return view('admin.vitrine.edit', ['agence' => $agence, 'photosMax' => self::PHOTOS_MAX]);
    }

    public function update(Request $request)
    {
        $agence = $this->agence();
        $donnees = $request->validate([
            'slogan'             => 'nullable|string|max:160',
            'couleur'            => ['nullable', Couleurs::REGLE],
            'couleur_secondaire' => ['nullable', Couleurs::REGLE],
        ], ['couleur.regex' => 'La couleur doit être au format #RRGGBB.', 'couleur_secondaire.regex' => 'La couleur doit être au format #RRGGBB.']);

        $agence->update([
            'slogan'             => $donnees['slogan'] ?? null,
            'couleur'            => isset($donnees['couleur']) ? strtoupper($donnees['couleur']) : null,
            'couleur_secondaire' => isset($donnees['couleur_secondaire']) ? strtoupper($donnees['couleur_secondaire']) : null,
        ]);
        Audit::log('admin.vitrine.modifiee', $agence, $agence->only(['slogan', 'couleur', 'couleur_secondaire']));

        return back()->with('success', 'Vitrine enregistrée. Elle apparaît dans l\'application.');
    }

    public function logo(Request $request)
    {
        $agence = $this->agence();
        $request->validate(['image' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048'],
            ['image.max' => "L'image ne doit pas dépasser 2 Mo."]);

        $agence->remplacerLogo($request->file('image'));
        Audit::log('admin.vitrine.logo', $agence);

        return back()->with('success', 'Logo mis à jour.');
    }

    public function ajouterPhoto(Request $request)
    {
        $agence = $this->agence();
        $request->validate(['image' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048'],
            ['image.max' => "L'image ne doit pas dépasser 2 Mo."]);

        if ($agence->photos()->count() >= self::PHOTOS_MAX) {
            return back()->withErrors(['image' => 'Maximum ' . self::PHOTOS_MAX . ' photos : supprimez-en une d\'abord.']);
        }

        $ordre = (int) $agence->photos()->max('ordre') + 1;
        Media::depuisFichier($request->file('image'), $agence->id, 'photo', $ordre);
        Audit::log('admin.vitrine.photo_ajoutee', $agence);

        return back()->with('success', 'Photo ajoutée.');
    }

    public function supprimerPhoto(int $media)
    {
        $agence = $this->agence();
        $photo = Media::where('id', $media)->where('agence_id', $agence->id)->where('type', 'photo')->firstOrFail();
        $photo->delete();
        Audit::log('admin.vitrine.photo_supprimee', $agence);

        return back()->with('success', 'Photo supprimée.');
    }
}
