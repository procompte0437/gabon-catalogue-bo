<?php

namespace App\Http\Controllers;

use App\Models\ArticleImage;

class MediaController extends Controller
{
    /** Sert une photo stockée en base : /media/<key>.jpg */
    public function show(string $key)
    {
        $image = ArticleImage::query()->where('key', $key)->firstOrFail();

        // La clé change à chaque nouvelle photo : le contenu peut être mis en cache longtemps.
        return response($image->bytes(), 200, [
            'Content-Type' => $image->mime,
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }
}
