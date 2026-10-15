<?php

namespace App\Support;

use Illuminate\Support\Facades\URL;

/**
 * En production, toutes les adresses générées (QR codes des billets, images
 * des agences, liens) utilisent le domaine public APP_URL
 * (https://mobembo-line.site), quel que soit le domaine par lequel la
 * requête est arrivée (ex. l'adresse Cloud Run des anciennes versions de
 * l'application). Les QR codes restent ainsi valables même si
 * l'hébergement change.
 */
class UrlPublique
{
    public static function appliquer(?string $appUrl): void
    {
        if (! $appUrl || ! str_starts_with($appUrl, 'https://')) {
            return;
        }
        URL::forceRootUrl(rtrim($appUrl, '/'));
        URL::forceScheme('https');
    }
}
