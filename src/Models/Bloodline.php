<?php

namespace NicolasKion\SDE\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use NicolasKion\SDE\ClassResolver;

class Bloodline extends Model
{
    public $incrementing = false;

    protected $fillable = [
        'name',
        'description',
        'race_id',
        'ship_type_id',
        'willpower',
        'perception',
        'charisma',
        'intelligence',
        'memory',
    ];

    public function race(): BelongsTo
    {
        return $this->belongsTo(ClassResolver::race());
    }

    public function shipType(): BelongsTo
    {
        return $this->belongsTo(ClassResolver::type());
    }

    public function characters(): HasMany
    {
        return $this->hasMany(ClassResolver::character(), 'bloodline_id');
    }
}
