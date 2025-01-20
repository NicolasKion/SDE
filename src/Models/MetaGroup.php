<?php

namespace NicolasKion\SDE\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use NicolasKion\SDE\ClassResolver;

class MetaGroup extends Model
{
    public $incrementing = false;

    protected $fillable = [
        'id',
        'name',
        'description',
        'icon_id',
        'icon_suffix',
    ];

    public function types(): HasMany
    {
        return $this->hasMany(ClassResolver::type(), 'meta_group_id');
    }

    public function icon(): BelongsTo
    {
        return $this->belongsTo(ClassResolver::icon());
    }
}
