<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SousRubrique extends Model
{
    protected $table = 'sous_rubriques';

    protected $fillable = [
        'rubrique_id',
        'position',
        'name',
        'code',
    ];

    protected function casts(): array
    {
        return [
            'position' => 'integer',
        ];
    }

    public function rubrique(): BelongsTo
    {
        return $this->belongsTo(Rubrique::class);
    }

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class)->orderBy('position');
    }

    public function toPublicArray(): array
    {
        return [
            'id' => $this->id,
            'position' => $this->position,
            'name' => $this->name,
        ];
    }
}
