<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MutaplasmidApplicableType extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'mutaplasmid_id',
        'input_type_id',
    ];

    /**
     * @return BelongsTo<Mutaplasmid, $this>
     */
    public function mutaplasmid(): BelongsTo
    {
        return $this->belongsTo(Mutaplasmid::class);
    }

    /**
     * @return BelongsTo<Type, $this>
     */
    public function inputType(): BelongsTo
    {
        return $this->belongsTo(Type::class, 'input_type_id');
    }
}
