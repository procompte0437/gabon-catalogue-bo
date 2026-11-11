<?php

use App\Http\Controllers\Api\CatalogueController;
use App\Http\Controllers\Api\ClientContentController;
use Illuminate\Support\Facades\Route;

Route::get('/catalogue', [CatalogueController::class, 'show']);
Route::get('/rubriques/{rubrique}/sous-rubriques', [CatalogueController::class, 'sousRubriques']);
Route::get('/sous-rubriques/{sousRubrique}/articles', [CatalogueController::class, 'articles']);

Route::middleware('throttle:10,1')->group(function () {
    Route::put('/sous-rubriques/{sousRubrique}', [ClientContentController::class, 'updateSous']);
    Route::post('/articles/{article}', [ClientContentController::class, 'updateArticle']);
});
