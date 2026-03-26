<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mutaplasmid extends Model
{
    public $incrementing = false;

    protected $fillable = [
        'id',
        'output_type_id',
    ];

    /**
     * @return BelongsTo<Type, $this>
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(Type::class, 'id');
    }

    /**
     * @return BelongsTo<Type, $this>
     */
    public function outputType(): BelongsTo
    {
        return $this->belongsTo(Type::class, 'output_type_id');
    }

    /**
     * @return HasMany<MutaplasmidAttribute, $this>
     */
    public function attributes(): HasMany
    {
        return $this->hasMany(MutaplasmidAttribute::class);
    }

    /**
     * @return HasMany<MutaplasmidApplicableType, $this>
     */
    public function applicableTypes(): HasMany
    {
        return $this->hasMany(MutaplasmidApplicableType::class);
    }
}
