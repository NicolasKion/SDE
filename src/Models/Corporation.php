<?php

namespace NicolasKion\SDE\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use NicolasKion\SDE\ClassResolver;

class Corporation extends Model
{
    public $incrementing = false;

    protected $fillable = [
        'id',
        'name',
        'ceo_id',
        'creator_id',
        'faction_id',
        'home_station_id',
        'member_count',
        'shares',
        'date_founded',
        'description',
        'url',
        'tax_rate',
        'war_eligible',
        'npc',
        'alliance_id'
    ];

    public function ceo(): BelongsTo
    {
        return $this->belongsTo(ClassResolver::character());
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(ClassResolver::character());
    }

    public function faction(): BelongsTo
    {
        return $this->belongsTo(ClassResolver::faction());
    }

    public function homeStation(): BelongsTo
    {
        return $this->belongsTo(ClassResolver::station());
    }

    public function alliance(): BelongsTo
    {
        return $this->belongsTo(ClassResolver::alliance());
    }

    protected function casts(): array
    {
        return [
            'date_founded' => 'datetime',
            'war_eligible' => 'boolean',
            'npc' => 'boolean',
        ];
    }
}
