<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Commands\Seed;

use Illuminate\Support\Facades\Storage;
use NicolasKion\SDE\ClassResolver;
use NicolasKion\SDE\Data\Dto\RaceDto;
use NicolasKion\SDE\Support\JSONL;

/**
 * @phpstan-type RacesFile array{
 *     _key: int,
 *     name: array{en:string|null},
 *     description: array{en: string|null},
 *     iconID: int|null
 * }[]
 */
class SeedRacesCommand extends BaseSeedCommand
{
    protected const string RACES_FILE = 'sde/races.jsonl';

    protected $signature = 'sde:seed:races';

    public function handle(): int
    {
        $this->startMemoryTracking();

        $race = ClassResolver::race();

        $count = $this->streamUpsert(
            $race::query(),
            JSONL::lazy(Storage::path(self::RACES_FILE), RaceDto::class),
            function (RaceDto $dto) {
                return [
                    'id' => $dto->id,
                    'name' => $dto->name,
                    'description' => $dto->description,
                    'icon_id' => $dto->iconId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            },
            ['id'],
            ['name', 'description', 'icon_id', 'updated_at'],
            'Seeding Races'
        );

        $this->displayMemoryStats($count);

        return self::SUCCESS;
    }
}
