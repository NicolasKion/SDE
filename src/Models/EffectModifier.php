<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Models;

use Illuminate\Database\Eloquent\Model;

class EffectModifier extends Model
{
    protected $table = 'effect_modifiers';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'effect_id',
        'domain',
        'func',
        'modified_attribute_id',
        'modifying_attribute_id',
        'operator',
        'group_id',
        'skill_type_id',
    ];
}
