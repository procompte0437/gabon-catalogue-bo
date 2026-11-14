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
        'is_linked',
        'is_visible',
    ];

    protected function casts(): array
    {
        return [
            'position' => 'integer',
            'is_linked' => 'boolean',
            'is_visible' => 'boolean',
        ];
    }

    /** Ne garde que les sous-rubriques affichées dans le catalogue. */
    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
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
            'is_linked' => (bool) $this->is_linked,
        ];
    }
}
