<?php

use App\Http\Controllers\Api\AdminCodeController;
use App\Http\Controllers\Api\CatalogueController;
use App\Http\Controllers\Api\ClientContentController;
use Illuminate\Support\Facades\Route;

Route::get('/catalogue', [CatalogueController::class, 'show']);
Route::get('/rubriques/{rubrique}/sous-rubriques', [CatalogueController::class, 'sousRubriques']);
Route::get('/sous-rubriques/{sousRubrique}/articles', [CatalogueController::class, 'articles']);

Route::middleware('throttle:10,1')->group(function () {
    Route::put('/rubriques/{rubrique}', [ClientContentController::class, 'updateRubrique']);
    Route::put('/sous-rubriques/{sousRubrique}', [ClientContentController::class, 'updateSous']);
    Route::post('/articles/{article}', [ClientContentController::class, 'updateArticle']);
});

// Mode administrateur (cadenas) : protégé par le mot de passe général.
Route::post('/admin/login', [AdminCodeController::class, 'login'])->middleware('throttle:10,1');
Route::middleware('throttle:60,1')->prefix('admin')->group(function () {
    Route::post('/rubriques', [AdminCodeController::class, 'storeRubrique']);
    Route::delete('/rubriques/{rubrique}', [AdminCodeController::class, 'destroyRubrique']);
    Route::post('/rubriques/{rubrique}/codes', [AdminCodeController::class, 'sousCodes']);
    Route::post('/sous-rubriques/{sousRubrique}/codes', [AdminCodeController::class, 'articleCodes']);
    Route::put('/sous-rubriques/{sousRubrique}/codes', [AdminCodeController::class, 'saveArticleCodes']);
});
