<?php

namespace NicolasKion\SDE\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use NicolasKion\SDE\ClassResolver;

class Alliance extends Model
{
    public $incrementing = false;

    protected $fillable = [
        'id',
        'name',
        'ticker',
        'creator_id',
        'creator_corporation_id',
        'faction_id',
        'date_founded',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(ClassResolver::character());
    }

    public function creatorCorporation(): BelongsTo
    {
        return $this->belongsTo(ClassResolver::corporation());
    }

    public function faction(): BelongsTo
    {
        return $this->belongsTo(ClassResolver::faction());
    }

    public function corporations(): HasMany
    {
        return $this->hasMany(ClassResolver::corporation(), 'alliance_id');
    }

    public function characters(): HasMany
    {
        return $this->hasMany(ClassResolver::character(), 'alliance_id');
    }

    protected function casts(): array
    {
        return [
            'date_founded' => 'datetime'
        ];
    }
}
