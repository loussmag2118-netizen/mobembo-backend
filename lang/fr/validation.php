<?php

return [
    'required' => 'Le champ :attribute est obligatoire.',
    'in'       => 'La valeur sélectionnée pour :attribute est invalide.',
    'string'   => 'Le champ :attribute doit être une chaîne de caractères.',
    'integer'  => 'Le champ :attribute doit être un nombre entier.',
    'numeric'  => 'Le champ :attribute doit être un nombre.',
    'date'     => 'Le champ :attribute n\'est pas une date valide.',
    'date_format' => 'Le champ :attribute ne correspond pas au format :format.',
    'unique'   => 'La valeur du champ :attribute est déjà utilisée.',
    'array'    => 'Le champ :attribute doit être une liste.',
    'size' => [
        'string'  => 'Le champ :attribute doit contenir exactement :size caractères.',
        'numeric' => 'Le champ :attribute doit être égal à :size.',
    ],
    'max' => [
        'string'  => 'Le champ :attribute ne peut pas dépasser :max caractères.',
        'numeric' => 'Le champ :attribute ne peut pas être supérieur à :max.',
    ],
    'min' => [
        'string'  => 'Le champ :attribute doit contenir au moins :min caractères.',
        'numeric' => 'Le champ :attribute doit être au moins :min.',
    ],

    'attributes' => [
        'numero_vol'         => 'numéro de vol',
        'origine'            => 'origine',
        'destination'        => 'destination',
        'ville_depart'       => 'ville de départ',
        'ville_arrivee'      => 'ville d\'arrivée',
        'ville_depart_iata'  => 'code IATA de départ',
        'ville_arrivee_iata' => 'code IATA d\'arrivée',
        'date_depart'        => 'date de départ',
        'heure_depart'       => 'heure de départ',
        'date_arrivee'       => 'date d\'arrivée',
        'heure_arrivee'      => 'heure d\'arrivée',
        'prix'               => 'prix',
        'places_disponibles' => 'places disponibles',
        'statut'             => 'statut',
        'type_vol'           => 'type de vol',
        'compagnie'          => 'compagnie',
    ],
];