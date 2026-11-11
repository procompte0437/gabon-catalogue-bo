<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Rubrique;
use App\Models\Setting;
use App\Models\SousRubrique;

class DashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard', [
            'appName' => Setting::appName(),
            'whatsapp' => Setting::whatsappDisplay(),
            'linked' => Rubrique::query()->where('is_linked', true)->orderBy('position')->first(),
            'stats' => [
                'rubriques' => Rubrique::query()->count(),
                'sous' => SousRubrique::query()->count(),
                'articles' => Article::query()->count(),
                'photos' => Article::query()->whereNotNull('image_path')->count(),
                'links' => Article::query()->whereNotNull('link')->where('link', '!=', '')->count(),
            ],
        ]);
    }
}
