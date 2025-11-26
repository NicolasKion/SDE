<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Commands\Seed;

use Illuminate\Support\Facades\Storage;
use NicolasKion\SDE\ClassResolver;
use NicolasKion\SDE\Data\Dto\UnitDto;
use NicolasKion\SDE\Support\JSONL;

/**
 * @phpstan-type UnitsFile array<int, array{
 *     _key: int,
 *     name: string,
 *     description: array{en: string}|null,
 *     displayName: array{en: string}|null,
 * }>
 */
class SeedUnitsCommand extends BaseSeedCommand
{
    protected const string UNITS_FILE = 'sde/dogmaUnits.jsonl';

    protected $signature = 'sde:seed:units';

    public function handle(): int
    {
        $this->startMemoryTracking();

        $unit = ClassResolver::unit();

        $count = $this->streamUpsert(
            $unit::query(),
            JSONL::lazy(Storage::path(self::UNITS_FILE), UnitDto::class),
            function (UnitDto $dto) {
                return [
                    'id' => $dto->id,
                    'name' => $dto->name,
                    'description' => $dto->description,
                    'display_name' => $dto->displayName,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            },
            ['id'],
            ['display_name', 'description', 'name', 'updated_at'],
            'Seeding Units'
        );

        $this->displayMemoryStats($count);

        return self::SUCCESS;
    }
}
