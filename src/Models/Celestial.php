<?php

namespace NicolasKion\SDE\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use NicolasKion\SDE\ClassResolver;

class Celestial extends Model
{
    public $incrementing = false;

    protected $fillable = [
        'name',
        'solarsystem_id',
        'constellation_id',
        'region_id',
        'parent_id',
        'type_id',
        'group_id',
    ];
    
    public function region(): BelongsTo
    {
        return $this->belongsTo(ClassResolver::region());
    }

    public function constellation(): BelongsTo
    {
        return $this->belongsTo(ClassResolver::constellation());
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(ClassResolver::type());
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(ClassResolver::group());
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ClassResolver::celestial());
    }

    public function stations(): HasMany
    {
        return $this->hasMany(ClassResolver::station(), 'parent_id');
    }
}
