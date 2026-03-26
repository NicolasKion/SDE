<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MutaplasmidAttribute extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'mutaplasmid_id',
        'attribute_id',
        'min',
        'max',
        'high_is_good',
    ];

    protected function casts(): array
    {
        return [
            'min' => 'float',
            'max' => 'float',
            'high_is_good' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Mutaplasmid, $this>
     */
    public function mutaplasmid(): BelongsTo
    {
        return $this->belongsTo(Mutaplasmid::class);
    }
}
