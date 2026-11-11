<?php

use App\Http\Controllers\Admin\ArticleController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\RubriqueController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\SousRubriqueController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('admin.dashboard')
        : redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/connexion', [AuthController::class, 'show'])->name('login');
    Route::post('/connexion', [AuthController::class, 'login'])->name('login.attempt');
});

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::post('/deconnexion', [AuthController::class, 'logout'])->name('logout');
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/rubriques', [RubriqueController::class, 'index'])->name('rubriques.index');
    Route::get('/rubriques/{rubrique}', [RubriqueController::class, 'show'])->name('rubriques.show');
    Route::put('/rubriques/{rubrique}', [RubriqueController::class, 'update'])->name('rubriques.update');

    Route::get('/sous-rubriques/{sousRubrique}', [SousRubriqueController::class, 'show'])->name('sous.show');
    Route::put('/sous-rubriques/{sousRubrique}', [SousRubriqueController::class, 'update'])->name('sous.update');
    Route::put('/sous-rubriques/{sousRubrique}/codes', [SousRubriqueController::class, 'updateCodes'])->name('sous.codes');

    Route::get('/articles/{article}/modifier', [ArticleController::class, 'edit'])->name('articles.edit');
    Route::put('/articles/{article}', [ArticleController::class, 'update'])->name('articles.update');
    Route::delete('/articles/{article}/photo', [ArticleController::class, 'destroyPhoto'])->name('articles.photo.destroy');

    Route::get('/reglages', [SettingController::class, 'edit'])->name('settings.edit');
    Route::put('/reglages', [SettingController::class, 'update'])->name('settings.update');
});
