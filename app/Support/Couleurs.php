<?php

namespace App\Support;

/**
 * Outils pour les couleurs des agences (#RRGGBB).
 */
class Couleurs
{
    public const REGLE = 'regex:/^#[0-9A-Fa-f]{6}$/';

    /** Mélange une couleur avec du noir (quantite > 0) ou du blanc (quantite < 0). */
    public static function nuancer(string $hex, float $quantite): string
    {
        [$r, $v, $b] = self::rvb($hex);
        $cible = $quantite >= 0 ? 0 : 255;
        $q = min(1, abs($quantite));
        $m = fn ($c) => (int) round($c + ($cible - $c) * $q);

        return sprintf('#%02X%02X%02X', $m($r), $m($v), $m($b));
    }

    /** Contraste (WCAG) entre la couleur et un texte blanc. */
    public static function contrasteBlanc(string $hex): float
    {
        $lin = function ($c) {
            $c /= 255;

            return $c <= 0.03928 ? $c / 12.92 : (($c + 0.055) / 1.055) ** 2.4;
        };
        [$r, $v, $b] = self::rvb($hex);
        $l = 0.2126 * $lin($r) + 0.7152 * $lin($v) + 0.0722 * $lin($b);

        return 1.05 / ($l + 0.05);
    }

    /**
     * Version de la couleur sur laquelle un texte blanc reste lisible :
     * une couleur claire (ex. jaune) est foncée juste ce qu'il faut.
     */
    public static function pourTexteBlanc(string $hex, float $contrasteMin = 3.6): string
    {
        $c = strtoupper($hex);
        for ($i = 0; $i < 12 && self::contrasteBlanc($c) < $contrasteMin; $i++) {
            $c = self::nuancer($c, 0.08);
        }

        return $c;
    }

    /** « r, g, b » pour les rgba() du CSS. */
    public static function rvbTexte(string $hex): string
    {
        return implode(', ', self::rvb($hex));
    }

    private static function rvb(string $hex): array
    {
        $h = ltrim($hex, '#');

        return [hexdec(substr($h, 0, 2)), hexdec(substr($h, 2, 2)), hexdec(substr($h, 4, 2))];
    }
}
