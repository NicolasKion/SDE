<?php

namespace NicolasKion\SDE\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use NicolasKion\SDE\ClassResolver;

class Solarsystem extends Model
{
    public $incrementing = false;

    protected $fillable = [
        'id',
        'name',
        'constellation_id',
        'region_id',
        'security',
        'pos_x',
        'pos_y',
        'pos_z',
        'type',
    ];

    public function region(): BelongsTo
    {
        return $this->belongsTo(ClassResolver::region());
    }

    public function constellation(): BelongsTo
    {
        return $this->belongsTo(ClassResolver::constellation());
    }

    public function celestials(): HasMany
    {
        return $this->hasMany(ClassResolver::celestial(), 'solarsystem_id');
    }

    public function stations(): HasMany
    {
        return $this->hasMany(ClassResolver::station(), 'solarsystem_id');
    }
}
