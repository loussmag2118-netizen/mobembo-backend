<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Airtel Money — Credentials
    |--------------------------------------------------------------------------
    |
    | Ces credentials viennent de ton compte développeur Airtel :
    | https://developers.airtel.africa
    |
    | - client_id     : ID client fourni par Airtel
    | - client_secret : Secret client fourni par Airtel
    | - pin           : PIN du compte marchand (pour les décaissements)
    |
    */

    'client_id'     => env('AIRTEL_CLIENT_ID', ''),
    'client_secret' => env('AIRTEL_CLIENT_SECRET', ''),
    'pin'           => env('AIRTEL_PIN', ''),

    /*
    |--------------------------------------------------------------------------
    | URLs de l'API Airtel Money
    |--------------------------------------------------------------------------
    |
    | Sandbox  : pour les tests (développement)
    | Production : pour le vrai argent
    |
    | Ces URLs sont utilisées par AirtelMoneyController.
    | Tu peux changer d'environnement via AIRTEL_ENV dans .env
    |
    */

    'urls' => [

        'sandbox' => [
            'base'  => 'https://openapiuat.airtel.africa',
            'token' => 'https://openapiuat.airtel.africa/auth/oauth2/token',
            'pay'   => 'https://openapiuat.airtel.africa/merchant/v1/payments/',
            'status'=> 'https://openapiuat.airtel.africa/standard/v1/payments/',
        ],

        'production' => [
            'base'  => 'https://openapi.airtel.africa',
            'token' => 'https://openapi.airtel.africa/auth/oauth2/token',
            'pay'   => 'https://openapi.airtel.africa/merchant/v1/payments/',
            'status'=> 'https://openapi.airtel.africa/standard/v1/payments/',
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Environnement courant
    |--------------------------------------------------------------------------
    |
    | Valeurs possibles : 'sandbox' ou 'production'
    | Défini via AIRTEL_ENV dans .env
    |
    */

    'environment' => env('AIRTEL_ENV', 'sandbox'),

    /*
    |--------------------------------------------------------------------------
    | Paramètres de la transaction
    |--------------------------------------------------------------------------
    |
    | country  : code pays (CG = Congo)
    | currency : devise (XAF = Franc CFA)
    |
    */

    'country'  => env('AIRTEL_COUNTRY', 'CG'),
    'currency' => env('AIRTEL_CURRENCY', 'XAF'),

    /*
    |--------------------------------------------------------------------------
    | Mode Simulation
    |--------------------------------------------------------------------------
    |
    | Si true → ne contacte PAS Airtel, crée les tickets directement.
    | Idéal en développement quand les credentials Airtel ne sont pas prêts.
    |
    | En production : mets AIRTEL_SIMULATION=false
    |
    */

    'simulation' => env('AIRTEL_SIMULATION', env('SIMULATION_MODE', true)),

];