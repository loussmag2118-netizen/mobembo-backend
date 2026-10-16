<?php

namespace App\Support;

use App\Models\Vol;
use Carbon\Carbon;

/**
 * Présentation d'un vol pour l'application mobile (clés en snake_case et
 * camelCase, lues par les différents écrans), avec la compagnie qui
 * l'opère : nom, logo et couleurs.
 */
class FormatVol
{
    public static function pourMobile(?Vol $vol): array
    {
        if (! $vol) {
            return [];
        }

        $depart  = $vol->date_depart ? Carbon::parse($vol->date_depart) : null;
        $arrivee = $vol->date_arrivee ? Carbon::parse($vol->date_arrivee) : null;

        $duree = '';
        if ($depart && $arrivee) {
            $minutes = (int) $depart->diffInMinutes($arrivee);
            $duree = sprintf('%dh%02d', intdiv($minutes, 60), $minutes % 60);
        }
        $dateFr = $depart ? $depart->locale('fr')->isoFormat('dddd D MMMM YYYY') : '';

        $partenaire = $vol->partenaire;
        $nomCompagnie = $partenaire?->nom_agence ?? ($vol->compagnie ?: 'Compagnie aérienne');

        return [
            'id'                 => $vol->id,
            'numero_vol'         => $vol->numero_vol ?? '',
            'numeroVol'          => $vol->numero_vol ?? '',
            'ville_depart'       => $vol->ville_depart ?? '',
            'villeDepart'        => $vol->ville_depart ?: ($vol->origine ?? ''),
            'ville_arrivee'      => $vol->ville_arrivee ?? '',
            'villeArrivee'       => $vol->ville_arrivee ?: ($vol->destination ?? ''),
            'ville_depart_iata'  => $vol->ville_depart_iata,
            'ville_arrivee_iata' => $vol->ville_arrivee_iata,
            'origine'            => $vol->origine ?? '',
            'destination'        => $vol->destination ?? '',
            'heure_depart'       => $depart ? $depart->format('H:i') : '',
            'heureDepart'        => $depart ? $depart->format('H:i') : '',
            'heure_arrivee'      => $arrivee ? $arrivee->format('H:i') : '',
            'heureArrivee'       => $arrivee ? $arrivee->format('H:i') : '',
            'date_depart'        => $depart?->format('Y-m-d H:i:s'),
            'date_arrivee'       => $arrivee?->format('Y-m-d H:i:s'),
            'date_formatee'      => $dateFr,
            'dateFormatee'       => $dateFr,
            'duree_formatee'     => $duree,
            'dureeFormatee'      => $duree,
            'prix'               => (float) ($vol->prix ?? 0),
            'prix_base'          => (float) ($vol->prix ?? 0),
            'prixBase'           => (float) ($vol->prix ?? 0),
            'places_disponibles' => (int) ($vol->places_disponibles ?? 0),
            'placesRestantes'    => (int) ($vol->places_disponibles ?? 0),
            'statut'             => $vol->statut ?? 'programme',
            'type_vol'           => $vol->type_vol ?? 'direct',
            'typeVol'            => $vol->type_vol ?? 'direct',
            'escales'            => $vol->escales ?? [],
            'inclusions'         => $vol->inclusions ?? [],
            'avion'              => $vol->avion,
            // Compagnie qui opère le vol
            'compagnie'          => $nomCompagnie,
            'compagnie_id'       => $vol->agence_id,
            'compagnie_info'     => $partenaire ? [
                'id'                 => $partenaire->id,
                'nom'                => $partenaire->nom_agence,
                'logo_url'           => $partenaire->logoPublicUrl(),
                'couleur'            => $partenaire->couleur,
                'couleur_secondaire' => $partenaire->couleur_secondaire,
                'slogan'             => $partenaire->slogan,
            ] : null,
        ];
    }
}
