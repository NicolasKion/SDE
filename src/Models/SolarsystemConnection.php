<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use NicolasKion\SDE\ClassResolver;

class SolarsystemConnection extends Model
{
    public $incrementing = false;

    protected $fillable = [
        'id',
        'from_stargate_id',
        'from_solarsystem_id',
        'from_constellation_id',
        'from_region_id',
        'to_stargate_id',
        'to_solarsystem_id',
        'to_constellation_id',
        'to_region_id',
        'is_regional',
    ];

    /**
     * @return BelongsTo<Solarsystem,$this>
     */
    public function fromSolarsystem(): BelongsTo
    {
        return $this->belongsTo(ClassResolver::solarsystem(), 'from_solarsystem_id');
    }

    /**
     * @return BelongsTo<Solarsystem,$this>
     */
    public function toSolarsystem(): BelongsTo
    {
        return $this->belongsTo(ClassResolver::solarsystem(), 'to_solarsystem_id');
    }

    /**
     * @return BelongsTo<Stargate,$this>
     */
    public function fromStargate(): BelongsTo
    {
        return $this->belongsTo(ClassResolver::stargate(), 'from_stargate_id');
    }

    /**
     * @return BelongsTo<Stargate,$this>
     */
    public function toStargate(): BelongsTo
    {
        return $this->belongsTo(ClassResolver::stargate(), 'to_stargate_id');
    }

    /**
     * @return BelongsTo<Constellation,$this>
     */
    public function fromConstellation(): BelongsTo
    {
        return $this->belongsTo(ClassResolver::constellation(), 'from_constellation_id');
    }

    /**
     * @return BelongsTo<Constellation,$this>
     */
    public function toConstellation(): BelongsTo
    {
        return $this->belongsTo(ClassResolver::constellation(), 'to_constellation_id');
    }

    /**
     * @return BelongsTo<Region,$this>
     */
    public function fromRegion(): BelongsTo
    {
        return $this->belongsTo(ClassResolver::region(), 'from_region_id');
    }

    /**
     * @return BelongsTo<Region,$this>
     */
    public function toRegion(): BelongsTo
    {
        return $this->belongsTo(ClassResolver::region(), 'to_region_id');
    }

    protected function casts(): array
    {
        return [
            'is_regional' => 'boolean',
        ];
    }
}
