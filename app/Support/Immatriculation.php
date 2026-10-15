<?php

namespace App\Support;

/**
 * Plaques d'immatriculation de la République du Congo (format CEMAC « RC »,
 * arrêté n°5564 du 18 juin 2004) : 3 chiffres d'ordre, 2 lettres de série
 * et le numéro de la circonscription (1 à 16), ex. « 123 AB 4 »
 * (4 = Brazzaville, 6 = Pointe-Noire).
 */
class Immatriculation
{
    public const CIRCONSCRIPTIONS = [
        1 => 'Commune de Nkayi',
        2 => 'Département de la Lékoumou',
        3 => 'Commune de Ouesso',
        4 => 'Département de Brazzaville',
        5 => 'Département du Kouilou',
        6 => 'Commune de Pointe-Noire',
        7 => 'Département de la Likouala',
        8 => 'Département de la Cuvette',
        9 => 'Département du Niari',
        10 => 'Commune de Dolisie',
        11 => 'Département de la Bouenza',
        12 => 'Département du Pool',
        13 => 'Département de la Sangha',
        14 => 'Département des Plateaux',
        15 => 'Commune de Mossendjo',
        16 => 'Département de la Cuvette-Ouest',
    ];

    public const MESSAGE = 'Plaque invalide : 3 chiffres, 2 lettres et le numéro de circonscription (1 à 16), ex. 123 AB 4.';

    /**
     * Forme officielle « 123 AB 4 » de la saisie (espaces, tirets, points,
     * minuscules et « RC » acceptés), ou null si ce n'est pas une plaque valide.
     */
    public static function normaliser(?string $saisie): ?string
    {
        $compacte = self::compacte((string) $saisie);
        $compacte = preg_replace('/^RC(?=\d)/', '', $compacte);

        if (! preg_match('/^(\d{3})([A-Z]{2})(\d{1,2})$/', $compacte, $m)) {
            return null;
        }
        $zone = (int) $m[3];
        if (! isset(self::CIRCONSCRIPTIONS[$zone]) || $m[3] !== (string) $zone) {
            return null;
        }

        return "{$m[1]} {$m[2]} {$zone}";
    }

    /** Plaque sans séparateurs, en majuscules (pour comparer deux saisies). */
    public static function compacte(string $plaque): string
    {
        return preg_replace('/[^A-Z0-9]/', '', strtoupper(trim($plaque)));
    }

    public static function circonscription(string $plaque): ?string
    {
        $n = self::normaliser($plaque);

        return $n ? self::CIRCONSCRIPTIONS[(int) substr($n, strrpos($n, ' ') + 1)] : null;
    }
}
