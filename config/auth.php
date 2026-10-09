<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    */

    'defaults' => [
        'guard'     => 'web',
        'passwords' => 'utilisateurs',
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    */

    'guards' => [
        'web' => [
            'driver'   => 'session',
            'provider' => 'utilisateurs',
        ],

        'api' => [
            'driver'   => 'token',
            'provider' => 'utilisateurs',
            'hash'     => false,
        ],

        // ⭐ Guard Canadian pour l'ADMIN WEB (session)
        'canadian' => [
            'driver'   => 'session',
            'provider' => 'canadian_users',
        ],

        // ⭐ Guard Canadian pour l'API FLUTTER (Sanctum)
        'canadian-api' => [
            'driver'   => 'sanctum',
            'provider' => 'canadian_users',
        ],

        // ⭐ Guard SuperAdmin (session) — pour le portail /superadmin
        'superadmin' => [
            'driver'   => 'session',
            'provider' => 'superadmins',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    */

    'providers' => [
        'utilisateurs' => [
            'driver' => 'eloquent',
            'model'  => App\Models\Utilisateur::class,
        ],

        // ⭐ Provider Canadian
        'canadian_users' => [
            'driver' => 'eloquent',
            'model'  => App\Models\CanadianUser::class,
        ],

        // ⭐ Provider SuperAdmin
        'superadmins' => [
            'driver' => 'eloquent',
            'model'  => App\Models\SuperAdmin::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resetting Passwords
    |--------------------------------------------------------------------------
    */

    'passwords' => [
        'utilisateurs' => [
            'provider' => 'utilisateurs',
            'table'    => 'password_reset_tokens',
            'expire'   => 60,
            'throttle' => 60,
        ],

        'canadian_users' => [
            'provider' => 'canadian_users',
            'table'    => 'password_reset_tokens',
            'expire'   => 60,
            'throttle' => 60,
        ],

        // ⭐ Reset password SuperAdmin
        'superadmins' => [
            'provider' => 'superadmins',
            'table'    => 'password_reset_tokens',
            'expire'   => 60,
            'throttle' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Confirmation Timeout
    |--------------------------------------------------------------------------
    */

    'password_timeout' => 10800,

];