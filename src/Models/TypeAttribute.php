<?php

namespace NicolasKion\SDE\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use NicolasKion\SDE\ClassResolver;

class TypeAttribute extends Model
{
    protected $fillable = [
        'id',
        'type_id',
        'attribute_id',
        'value',
    ];

    public function type(): BelongsTo
    {
        return $this->belongsTo(ClassResolver::type());
    }

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(ClassResolver::attribute());
    }
}
