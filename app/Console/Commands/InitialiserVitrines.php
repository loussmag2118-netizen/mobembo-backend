<?php

namespace App\Console\Commands;

use App\Models\Agence;
use App\Models\Media;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Pré-remplit la vitrine des agences partenaires historiques avec les
 * images, couleurs et slogans de l'application mobile. Ne remplit que ce
 * qui est vide : ce que l'agence a déjà configuré n'est jamais écrasé.
 *
 *   php artisan vitrines:initialiser ../mobembo-line-mobile/assets/images
 */
class InitialiserVitrines extends Command
{
    protected $signature = 'vitrines:initialiser {photos : dossier des photos (assets/images de l\'application)}';

    protected $description = 'Pré-remplit la vitrine (logo, photos, couleur, slogan) des agences historiques';

    /** Nom de l'agence → [couleur, slogan, préfixe des photos] */
    private const AGENCES = [
        'Océan du Nord'   => ['#1B5E20', 'Sécurité, confort et fiabilité à chaque trajet.', 'oce'],
        'Transbony'       => ['#0D47A1', 'Votre trajet, notre engagement.', 'trans'],
        'Stelimac'        => ['#E64A19', 'Voyagez avec élégance et ponctualité.', 'stel'],
        'Nzoko Transport' => ['#F9A825', 'La route en toute confiance.', 'nzo'],
    ];

    public function handle(): int
    {
        $dossier = rtrim($this->argument('photos'), '/');

        foreach (self::AGENCES as $nom => [$couleur, $slogan, $prefixe]) {
            $agence = Agence::where('nom_agence', $nom)->first();
            if (! $agence) {
                $this->warn("$nom : agence introuvable.");
                continue;
            }

            $fait = DB::transaction(function () use ($agence, $couleur, $slogan, $prefixe, $dossier) {
                $fait = [];
                if (! $agence->couleur) {
                    $agence->couleur = $couleur;
                    $fait[] = 'couleur';
                }
                if (! $agence->slogan) {
                    $agence->slogan = $slogan;
                    $fait[] = 'slogan';
                }
                $logo = public_path('images/agences/' . Str::slug($agence->nom_agence) . '.jpg');
                if (! $agence->logo_media_id && is_file($logo)) {
                    $agence->logo_media_id = $this->media($agence->id, 'logo', 0, $logo)->id;
                    $fait[] = 'logo';
                }
                if (! $agence->photos()->exists()) {
                    $photos = glob("$dossier/{$prefixe}[0-9].jpg") ?: [];
                    sort($photos);
                    foreach ($photos as $i => $photo) {
                        $this->media($agence->id, 'photo', $i + 1, $photo);
                    }
                    if ($photos) {
                        $fait[] = count($photos) . ' photos';
                    }
                }
                $agence->save();

                return $fait;
            });

            $this->line("$nom : " . ($fait ? implode(', ', $fait) : 'déjà configurée'));
        }

        return self::SUCCESS;
    }

    private function media(int $agenceId, string $type, int $ordre, string $fichier): Media
    {
        return Media::create([
            'agence_id' => $agenceId,
            'type'      => $type,
            'ordre'     => $ordre,
            'mime'      => mime_content_type($fichier),
            'taille'    => filesize($fichier),
            'contenu'   => file_get_contents($fichier),
        ]);
    }
}
