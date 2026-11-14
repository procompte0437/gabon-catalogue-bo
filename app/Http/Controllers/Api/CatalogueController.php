<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Rubrique;
use App\Models\Setting;
use App\Models\SousRubrique;

class CatalogueController extends Controller
{
    public function show()
    {
        return response()->json([
            'app_name' => Setting::appName(),
            'whatsapp_display' => Setting::whatsappDisplay(),
            'whatsapp_link' => Setting::whatsappLink(),
            'rubriques' => Rubrique::query()->orderBy('position')->get()->map->toPublicArray()->values(),
        ]);
    }

    public function sousRubriques(Rubrique $rubrique)
    {
        return response()->json([
            'rubrique' => $rubrique->toPublicArray(),
            'sous_rubriques' => $rubrique->sousRubriques()->visible()->get()->map->toPublicArray()->values(),
        ]);
    }

    public function articles(SousRubrique $sousRubrique)
    {
        $sousRubrique->load('rubrique', 'articles');
        $linked = (bool) $sousRubrique->is_linked;

        // « rubrique.is_linked » vaut ici le réglage de la sous-rubrique : le site et l'APK
        // s'en servent pour afficher le champ « Lien du site » et ouvrir les liens.
        return response()->json([
            'rubrique' => ['is_linked' => $linked] + $sousRubrique->rubrique->toPublicArray(),
            'sous_rubrique' => $sousRubrique->toPublicArray(),
            'articles' => $sousRubrique->articles
                ->map(fn ($article) => $article->toPublicArray($linked))
                ->values(),
        ]);
    }
}
