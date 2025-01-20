<?php

namespace NicolasKion\SDE\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use NicolasKion\SDE\ClassResolver;

class Faction extends Model
{
    public $incrementing = false;

    protected $fillable = [
        'name',
        'description',
        'corporation_id',
        'militia_corporation_id',
        'solarsystem_id',
        'size_factor',
        'station_count',
        'station_system_count',
        'is_unique',
    ];

    public function militiaCorporation(): BelongsTo
    {
        return $this->belongsTo(ClassResolver::corporation());
    }

    public function corporation(): BelongsTo
    {
        return $this->belongsTo(ClassResolver::corporation());
    }

    public function corporations(): HasMany
    {
        return $this->hasMany(ClassResolver::corporation());
    }
}
