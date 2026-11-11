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

    'frontend_url' => env('FRONTEND_URL', 'http://127.0.0.1:5180'),

];
