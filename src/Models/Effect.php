<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use NicolasKion\SDE\ClassResolver;

class Effect extends Model
{
    protected $table = 'effects';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'description',
        'disallow_auto_repeat',
        'discharge_attribute_id',
        'display_name',
        'duration_attribute_id',
        'effect_category',
        'electronic_chance',
        'falloff_attribute_id',
        'icon_id',
        'is_assistance',
        'is_offensive',
        'is_warp_safe',
        'name',
        'post_expression',
        'pre_expression',
        'published',
        'range_attribute_id',
        'range_chance',
        'tracking_speed_attribute_id',
        'propulsion_chance',
        'resistance_attribute_id',
        'fitting_usage_chance_attribute_id',
    ];

    /**
     * @return HasMany<EffectModifier, $this>
     */
    public function effectModifiers(): HasMany
    {
        return $this->hasMany(ClassResolver::effectModifier());
    }
}
