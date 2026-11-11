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
        $rubrique->load('sousRubriques');

        return response()->json([
            'rubrique' => $rubrique->toPublicArray(),
            'sous_rubriques' => $rubrique->sousRubriques->map->toPublicArray()->values(),
        ]);
    }

    public function articles(SousRubrique $sousRubrique)
    {
        $sousRubrique->load('rubrique', 'articles');
        $linked = (bool) $sousRubrique->rubrique->is_linked;

        return response()->json([
            'rubrique' => $sousRubrique->rubrique->toPublicArray(),
            'sous_rubrique' => $sousRubrique->toPublicArray(),
            'articles' => $sousRubrique->articles
                ->map(fn ($article) => $article->toPublicArray($linked))
                ->values(),
        ]);
    }
}
