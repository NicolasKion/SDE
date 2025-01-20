<?php

namespace NicolasKion\SDE\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use NicolasKion\SDE\ClassResolver;

class Graphic extends Model
{
    public $incrementing = false;

    protected $fillable = [
        'id',
        'sof_faction_name',
        'file',
        'sof_hull_name',
        'sof_race_name',
        'description',
    ];

    public function types(): HasMany
    {
        return $this->hasMany(ClassResolver::type(), 'graphic_id');
    }
}
