<?php

namespace NicolasKion\SDE\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use NicolasKion\SDE\ClassResolver;

class Group extends Model
{
    public $incrementing = false;

    protected $fillable = [
        'id',
        'name',
        'category_id',
        'icon_id',
        'published',
        'use_base_price',
        'anchored',
        'anchorable',
        'fittable_non_singleton',
    ];

    public function icon(): BelongsTo
    {
        return $this->belongsTo(ClassResolver::icon());
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ClassResolver::category());
    }

    public function types(): HasMany
    {
        return $this->hasMany(ClassResolver::type(), 'group_id');
    }
}
