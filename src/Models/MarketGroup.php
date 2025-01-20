<?php

namespace NicolasKion\SDE\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use NicolasKion\SDE\ClassResolver;

class MarketGroup extends Model
{
    public $incrementing = false;

    protected $fillable = [
        'id',
        'name',
        'description',
        'has_types',
        'icon_id',
        'parent_id',
    ];

    public function types(): HasMany
    {
        return $this->hasMany(ClassResolver::type(), 'market_group_id');
    }

    public function icon(): BelongsTo
    {
        return $this->belongsTo(ClassResolver::icon());
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ClassResolver::marketGroup());
    }

    protected function casts(): array
    {
        return [
            'has_types' => 'bool'
        ];
    }
}
