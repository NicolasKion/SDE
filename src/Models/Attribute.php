<?php

namespace NicolasKion\SDE\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use NicolasKion\SDE\ClassResolver;

class Attribute extends Model
{
    public $incrementing = false;

    protected $fillable = [
        'id',
        'name',
        'display_name',
        'description',
        'default_value',
        'unit_id',
        'icon_id',
        'high_is_good',
        'published',
        'stackable',
    ];

    public function typeAttributes(): HasMany
    {
        return $this->hasMany(ClassResolver::typeAttribute(), 'attribute_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(ClassResolver::unit());
    }

    public function icon(): BelongsTo
    {
        return $this->belongsTo(ClassResolver::icon());
    }
}
