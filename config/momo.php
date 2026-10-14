<?php

return [

    /*
    |--------------------------------------------------------------------------
    | MTN MoMo - Collection (encaissement)
    |--------------------------------------------------------------------------
    */

    'collection' => [
        'api_user'           => env('MTN_COLLECTION_API_USER', ''),
        'api_key'            => env('MTN_COLLECTION_API_KEY', ''),
        'primary_key'        => env('MTN_COLLECTION_PRIMARY_KEY', ''),
        'secondary_key'      => env('MTN_COLLECTION_SECONDARY_KEY', ''),
        'target_environment' => env('MTN_TARGET_ENV', 'sandbox'),
    ],

    /*
    |--------------------------------------------------------------------------
    | MTN MoMo - Disbursement (décaissement / remboursement)
    |--------------------------------------------------------------------------
    */

    'disbursement' => [
        'api_user'           => env('MTN_DISBURSEMENT_API_USER', ''),
        'api_key'            => env('MTN_DISBURSEMENT_API_KEY', ''),
        'primary_key'        => env('MTN_DISBURSEMENT_PRIMARY_KEY', ''),
        'secondary_key'      => env('MTN_DISBURSEMENT_SECONDARY_KEY', ''),
        'target_environment' => env('MTN_TARGET_ENV', 'sandbox'),
    ],

    /*
    |--------------------------------------------------------------------------
    | URLs des APIs MTN MoMo
    |--------------------------------------------------------------------------
    */

    'urls' => [
        'sandbox' => [
            'base'  => 'https://sandbox.momodeveloper.mtn.com',
            'token' => 'https://sandbox.momodeveloper.mtn.com/collection/token/',
            'pay'   => 'https://sandbox.momodeveloper.mtn.com/collection/v1_0/requesttopay',
        ],
        'production' => [
            'base'  => 'https://proxy.momoapi.mtn.com',
            'token' => 'https://proxy.momoapi.mtn.com/collection/token/',
            'pay'   => 'https://proxy.momoapi.mtn.com/collection/v1_0/requesttopay',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Mode simulation
    |--------------------------------------------------------------------------
    | Si true : aucune requête n'est envoyée à MTN, un paiement simulé est
    | enregistré directement. Utile pour les tests en local.
    */

    'simulation' => env('MTN_SIMULATION', env('SIMULATION_MODE', false)),

    /*
    |--------------------------------------------------------------------------
    | Montant minimum (en FCFA)
    |--------------------------------------------------------------------------
    | Utilisé dans la validation de MomoController::requestToPay().
    | Mets 1 en local pour tester, 100 en production.
    */

    'min_amount' => env('MOMO_MIN_AMOUNT', 100),

    /*
    | Adresse de retour appelée par MTN à la fin d'un paiement (X-Callback-Url).
    | Ex. https://<api>/api/paiements/mtn/callback — vide = pas de callback,
    | le statut est alors obtenu en interrogeant MTN.
    */

    'callback_url' => env('MTN_CALLBACK_URL'),

];