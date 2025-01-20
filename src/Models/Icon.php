<?php

namespace NicolasKion\SDE\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use NicolasKion\SDE\ClassResolver;

class Icon extends Model
{
    public $incrementing = false;

    protected $fillable = [
        'id',
        'file',
        'description',
    ];

    public function types(): HasMany
    {
        return $this->hasMany(ClassResolver::type(), 'icon_id');
    }

    public function attributes(): HasMany
    {
        return $this->hasMany(ClassResolver::attribute(), 'icon_id');
    }

    public function marketGroups(): HasMany
    {
        return $this->hasMany(ClassResolver::marketGroup(), 'icon_id');
    }

    public function metaGroups(): HasMany
    {
        return $this->hasMany(ClassResolver::metaGroup(), 'icon_id');
    }
}
