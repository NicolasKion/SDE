<?php

namespace NicolasKion\SDE\Models;

use Illuminate\Database\Eloquent\Model;

class TypeEffect extends Model
{
    protected $table = 'type_effects';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'type_id',
        'effect_id',
        'is_default',
    ];
}
