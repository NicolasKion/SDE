<?php

namespace NicolasKion\SDE\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use NicolasKion\SDE\ClassResolver;

class Region extends Model
{
    public $incrementing = false;

    protected $fillable = [
        'id',
        'name',
        'type',
    ];

    public function constellations(): HasMany
    {
        return $this->hasMany(ClassResolver::constellation(), 'region_id');
    }

    public function solarsystems(): HasMany
    {
        return $this->hasMany(ClassResolver::solarsystem(), 'region_id');
    }

    public function celestials(): HasMany
    {
        return $this->hasMany(ClassResolver::celestial(), 'region_id');
    }

    public function stations(): HasMany
    {
        return $this->hasMany(ClassResolver::station(), 'region_id');
    }
}
