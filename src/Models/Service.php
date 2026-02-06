<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use NicolasKion\SDE\ClassResolver;

class Service extends Model
{
    public $incrementing = false;

    protected $fillable = [
        'id',
        'name',
    ];

    /**
     * @return BelongsToMany<StationOperation,$this>
     */
    public function operations(): BelongsToMany
    {
        return $this->belongsToMany(
            ClassResolver::stationOperation(),
            'operation_services',
            'service_id',
            'station_operation_id'
        );
    }
}
