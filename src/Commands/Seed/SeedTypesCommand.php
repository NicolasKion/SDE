<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Commands\Seed;

use Exception;
use Illuminate\Support\Facades\Storage;
use NicolasKion\SDE\ClassResolver;
use NicolasKion\SDE\Data\Dto\TypeDto;
use NicolasKion\SDE\Support\JSONL;

/**
 * @phpstan-type TypesFile array{
 *     _key: int,
 *     name: array{en: string|null},
 *     description: array{en: string|null},
 *     groupID: int,
 *     marketGroupID: int|null,
 *     published: boolean|null,
 *     mass: float|null,
 *     volume: float|null,
 *     capacity: float|null,
 *     portionSize: float|null,
 *     basePrice: float|null,
 *     radius: float|null,
 *     iconID: int|null,
 *     graphicID: int|null,
 *     metaGroupID: int|null,
 *     factionID: int|null,
 * }[]
 */
class SeedTypesCommand extends BaseSeedCommand
{
    protected const string TYPES_FILE = 'sde/types.jsonl';

    protected $signature = 'sde:seed:types';

    /**
     * @throws Exception
     */
    public function handle(): int
    {
        $this->ensureSDEExists();
        $this->startMemoryTracking();

        $type = ClassResolver::type();

        $count = $this->streamUpsert(
            $type::query(),
            JSONL::lazy(Storage::path(self::TYPES_FILE), TypeDto::class),
            function (TypeDto $dto) {
                return [
                    'id' => $dto->id,
                    'name' => $dto->name,
                    'description' => $dto->description,
                    'group_id' => $dto->groupId,
                    'market_group_id' => $dto->marketGroupId,
                    'graphic_id' => $dto->graphicId,
                    'meta_group_id' => $dto->metaGroupId,
                    'published' => $dto->published,
                    'mass' => $dto->mass,
                    'volume' => $dto->volume,
                    'capacity' => $dto->capacity,
                    'portion_size' => $dto->portionSize,
                    'base_price' => $dto->basePrice,
                    'radius' => $dto->radius,
                    'icon_id' => $dto->iconId,
                    'faction_id' => $dto->factionId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            },
            ['id'],
            ['name', 'description', 'group_id', 'market_group_id', 'graphic_id', 'meta_group_id', 'published', 'mass', 'volume', 'capacity', 'portion_size', 'base_price', 'radius', 'icon_id', 'faction_id', 'updated_at'],
            'Seeding Types'
        );

        $this->displayMemoryStats($count);

        return self::SUCCESS;
    }
}
