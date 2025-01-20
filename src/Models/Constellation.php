<?php

namespace NicolasKion\SDE\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use NicolasKion\SDE\ClassResolver;

class Constellation extends Model
{
    public $incrementing = false;

    protected $fillable = [
        'id',
        'name',
        'region_id',
        'type',
    ];

    public function region(): BelongsTo
    {
        return $this->belongsTo(ClassResolver::region());
    }

    public function solarsystems(): HasMany
    {
        return $this->hasMany(ClassResolver::solarsystem(), 'constellation_id');
    }

    public function stations(): HasMany
    {
        return $this->hasMany(ClassResolver::station(), 'constellation_id');
    }

    public function celestials(): HasMany
    {
        return $this->hasMany(ClassResolver::celestial(), 'constellation_id');
    }
}
