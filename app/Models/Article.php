<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Article extends Model
{
    protected $fillable = [
        'sous_rubrique_id',
        'position',
        'title',
        'image_path',
        'link',
        'code',
    ];

    protected function casts(): array
    {
        return [
            'position' => 'integer',
        ];
    }

    public function sousRubrique(): BelongsTo
    {
        return $this->belongsTo(SousRubrique::class);
    }

    public function imageUrl(): ?string
    {
        if (! $this->image_path) {
            return null;
        }

        return asset('storage/'.$this->image_path);
    }

    public function toPublicArray(bool $withLink): array
    {
        return [
            'id' => $this->id,
            'position' => $this->position,
            'title' => $this->title,
            'image_url' => $this->imageUrl(),
            'link' => $withLink ? $this->link : null,
        ];
    }
}
