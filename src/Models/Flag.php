<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Models;

use Illuminate\Database\Eloquent\Model;

class Flag extends Model
{
    protected $fillable = [
        'id',
        'name',
        'text',
        'order_id',
    ];
}
