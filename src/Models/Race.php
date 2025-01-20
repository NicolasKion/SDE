<?php

namespace NicolasKion\SDE\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use NicolasKion\SDE\ClassResolver;

class Race extends Model
{
    public $incrementing = false;

    protected $fillable = [
        'id',
        'name',
        'description',
        'short_description',
        'icon_id',
    ];

    public function types(): HasMany
    {
        return $this->hasMany(ClassResolver::type(), 'race_id');
    }

    public function characters(): HasMany
    {
        return $this->hasMany(ClassResolver::character(), 'race_id');
    }
}
