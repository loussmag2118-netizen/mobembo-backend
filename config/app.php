<?php

return [

    'name' => env('APP_NAME', 'Laravel'),

    // Adresse de contact affichée dans la politique de confidentialité
    'contact_email' => env('CONTACT_EMAIL', 'contact@mobembo-line.site'),

    'env' => env('APP_ENV', 'production'),

    'debug' => (bool) env('APP_DEBUG', false),

    'url' => env('APP_URL', 'http://localhost'),

    // Heure du Congo (UTC+1) : départs du jour, expiration des réservations…
    'timezone' => env('APP_TIMEZONE', 'Africa/Brazzaville'),

    'locale' => env('APP_LOCALE', 'en'),

    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),

    'faker_locale' => env('APP_FAKER_LOCALE', 'en_US'),

    'cipher' => 'AES-256-CBC',

    'key' => env('APP_KEY'),

    'previous_keys' => [
        ...array_filter(
            explode(',', (string) env('APP_PREVIOUS_KEYS', ''))
        ),
    ],

    'maintenance' => [
        'driver' => env('APP_MAINTENANCE_DRIVER', 'file'),
        'store' => env('APP_MAINTENANCE_STORE', 'database'),
    ],

];