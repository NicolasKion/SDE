<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use NicolasKion\SDE\ClassResolver;

class OperationService extends Model
{
    protected $fillable = [
        'station_operation_id',
        'service_id',
    ];

    /**
     * @return BelongsTo<StationOperation,$this>
     */
    public function operation(): BelongsTo
    {
        return $this->belongsTo(ClassResolver::stationOperation(), 'station_operation_id');
    }

    /**
     * @return BelongsTo<Service,$this>
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(ClassResolver::service());
    }
}
