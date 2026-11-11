<?php

return [

    /*
    | Structure fixe du catalogue, identique au prototype :
    | 15 rubriques × 30 sous-rubriques × 10 articles.
    | La rubrique en position 9 ouvre un site web au lieu de zoomer la photo.
    */

    'rubriques' => 15,
    'sous_rubriques' => 30,
    'articles' => 10,
    'linked_position' => 9,
    'default_code' => '0000',

    // Mot de passe général (comme l'ancien mode administrateur du prototype) : il ouvre
    // la modification de toutes les rubriques, sous-rubriques et articles. Modifiable dans Réglages.
    'master_code' => env('CATALOGUE_MASTER_CODE', '0110'),

    'frontend_url' => env('FRONTEND_URL', 'http://127.0.0.1:5180'),

    // Catalogue public en ligne, utilisé quand le back-office est ouvert en ligne
    // et que FRONTEND_URL pointe encore vers la machine locale.
    'frontend_url_online' => env('FRONTEND_URL_ONLINE', 'https://gabon-catalogue-production.up.railway.app'),

];
