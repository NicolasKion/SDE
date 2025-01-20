<?php

namespace NicolasKion\SDE\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use NicolasKion\SDE\ClassResolver;

class Character extends Model
{
    public $incrementing = false;

    protected $fillable = [
        'id',
        'name',
        'description',
        'race_id',
        'bloodline_id',
        'corporation_id',
        'faction_id',
        'alliance_id',
        'security_status',
        'gender',
        'birthday',
        'title',
    ];

    /**
     * @param int[] $ids
     * @return void
     */
    public static function createFromIds(array $ids): void
    {
        DB::transaction(fn() => self::query()->upsert(
            collect($ids)->map(fn($id) => ['id' => $id])->toArray(),
            ['id']
        ), 5);
    }

    public function race(): BelongsTo
    {
        return $this->belongsTo(ClassResolver::race());
    }

    public function bloodline(): BelongsTo
    {
        return $this->belongsTo(ClassResolver::bloodline());
    }

    public function corporation(): BelongsTo
    {
        return $this->belongsTo(ClassResolver::corporation());
    }

    public function faction(): BelongsTo
    {
        return $this->belongsTo(ClassResolver::faction());
    }

    public function alliance(): BelongsTo
    {
        return $this->belongsTo(ClassResolver::alliance());
    }
}
