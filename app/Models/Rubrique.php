<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rubrique extends Model
{
    protected $table = 'rubriques';

    protected $fillable = [
        'position',
        'name',
        'is_linked',
    ];

    protected function casts(): array
    {
        return [
            'is_linked' => 'boolean',
            'position' => 'integer',
        ];
    }

    public function sousRubriques(): HasMany
    {
        return $this->hasMany(SousRubrique::class)->orderBy('position');
    }

    public function toPublicArray(): array
    {
        return [
            'id' => $this->id,
            'position' => $this->position,
            'name' => $this->name,
            'is_linked' => $this->is_linked,
        ];
    }
}
