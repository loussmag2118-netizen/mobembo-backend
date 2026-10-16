<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Mode test des paiements (MTN MoMo / Airtel Money)
    |--------------------------------------------------------------------------
    |
    | En mode test, le paiement est simulé et réussit immédiatement : les
    | billets portent alors la mention « TEST » et ne sont pas valables pour
    | voyager en production.
    |
    | Hors production (APP_ENV ≠ production), le mode test est utilisé dès que
    | les clés d'un opérateur manquent ou qu'une simulation est demandée
    | (SIMULATION_MODE, MTN_SIMULATION, AIRTEL_SIMULATION).
    |
    | En production, aucun paiement n'est simulé, SAUF si PAIEMENT_MODE_TEST=true
    | est explicitement défini (phase de recette avant l'ouverture au public).
    | À retirer avant d'accepter de vrais clients.
    |
    */

    'mode_test_production' => (bool) env('PAIEMENT_MODE_TEST', false),

    /*
    | Durée de la réservation temporaire en attente de paiement (minutes).
    */

    'duree_reservation_minutes' => (int) env('RESERVATION_DUREE_MINUTES', 15),

    /*
    | Durée pendant laquelle une même clé d'idempotence renvoie le même
    | paiement au lieu d'en créer un nouveau (minutes).
    */

    'idempotence_minutes' => 30,

    /*
    | Bébés (0 à 3 ans) : ils voyagent sur les genoux d'un adulte, sans siège,
    | et paient seulement une assurance (FCFA). À partir de 4 ans, l'enfant
    | paie son billet et a sa place.
    */

    'assurance_bebe' => (int) env('ASSURANCE_BEBE', 5000),
    'age_max_bebe'   => 3,

];
