<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Photo d'article stockée en base (contenu JPEG encodé en base64 dans « data »).
 */
class ArticleImage extends Model
{
    protected $table = 'article_images';

    protected $fillable = [
        'key',
        'mime',
        'size',
        'data',
    ];

    protected $hidden = [
        'data',
    ];

    public function bytes(): string
    {
        return (string) base64_decode($this->data, true);
    }
}
