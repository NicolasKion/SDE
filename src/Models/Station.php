<?php

namespace NicolasKion\SDE\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use NicolasKion\SDE\ClassResolver;

class Station extends Model
{
    public $incrementing = false;

    protected $fillable = [
        'id',
        'name',
        'solarsystem_id',
        'constellation_id',
        'region_id',
        'parent_id',
        'type_id',
        'group_id',
    ];

    public function solarsystem(): BelongsTo
    {
        return $this->belongsTo(ClassResolver::solarsystem());
    }

    public function constellation(): BelongsTo
    {
        return $this->belongsTo(ClassResolver::constellation());
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ClassResolver::celestial());
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(ClassResolver::type());
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(ClassResolver::group());
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(ClassResolver::region());
    }
}
