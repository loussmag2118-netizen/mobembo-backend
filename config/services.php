<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel'              => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'twilio' => [
        'sid'        => env('TWILIO_SID'),
        'auth_token' => env('TWILIO_AUTH_TOKEN'),
        'token'      => env('TWILIO_AUTH_TOKEN'), // alias pour compatibilité
        'verify_sid' => env('TWILIO_VERIFY_SID'),
        'from'       => env('TWILIO_FROM'),
        // Message du code reçu par SMS (Twilio Verify)
        'verify_nom'          => env('TWILIO_VERIFY_NOM'),   // nom personnalisé (si le compte Twilio l'autorise), 30 caractères max
        'verify_locale'       => env('TWILIO_VERIFY_LOCALE', 'fr'),
        'verify_template_sid' => env('TWILIO_VERIFY_TEMPLATE_SID'),         // modèle personnalisé approuvé par Twilio (HJ…)
    ],

];