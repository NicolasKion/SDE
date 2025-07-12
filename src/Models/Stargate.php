<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use NicolasKion\SDE\ClassResolver;

class Stargate extends Model
{
    public $incrementing = false;

    protected $fillable = [
        'id',
        'solarsystem_id',
        'constellation_id',
        'region_id',
        'destination_id',
        'type_id',
        'position_x',
        'position_y',
        'position_z',
    ];

    /**
     * @return BelongsTo<Solarsystem,$this>
     */
    public function solarsystem(): BelongsTo
    {
        return $this->belongsTo(ClassResolver::solarsystem());
    }

    /**
     * @return BelongsTo<Constellation,$this>
     */
    public function constellation(): BelongsTo
    {
        return $this->belongsTo(ClassResolver::constellation());
    }

    /**
     * @return BelongsTo<Type,$this>
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(ClassResolver::type());
    }

    /**
     * @return BelongsTo<Region,$this>
     */
    public function region(): BelongsTo
    {
        return $this->belongsTo(ClassResolver::region());
    }

    /**
     * @return BelongsTo<Stargate,$this>
     */
    public function destination(): BelongsTo
    {
        return $this->belongsTo(ClassResolver::stargate(), 'destination_id');
    }
}
