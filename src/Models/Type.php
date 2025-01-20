<?php

namespace NicolasKion\SDE\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use NicolasKion\SDE\ClassResolver;

class Type extends Model
{
    public $incrementing = false;

    protected $fillable = [
        'id',
        'name',
        'description',
        'graphic_id',
        'group_id',
        'icon_id',
        'market_group_id',
        'meta_group_id',
        'race_id',
        'published',
        'capacity',
        'mass',
        'base_price',
        'volume',
        'packaged_volume',
        'radius',
        'portion_size',
    ];

    public function typeAttributes(): HasMany
    {
        return $this->hasMany(ClassResolver::typeAttribute(), 'type_id');
    }

    public function race(): BelongsTo
    {
        return $this->belongsTo(ClassResolver::race());
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(ClassResolver::group());
    }

    public function icon(): BelongsTo
    {
        return $this->belongsTo(ClassResolver::icon());
    }

    public function marketGroup(): BelongsTo
    {
        return $this->belongsTo(ClassResolver::marketGroup());
    }

    public function metaGroup(): BelongsTo
    {
        return $this->belongsTo(ClassResolver::metaGroup());
    }

    public function graphic(): BelongsTo
    {
        return $this->belongsTo(ClassResolver::graphic());
    }
}
