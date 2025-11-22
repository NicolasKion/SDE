<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Commands\Seed;

use Exception;
use Illuminate\Support\Facades\Storage;
use NicolasKion\SDE\ClassResolver;
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

        $this->info(sprintf('Parsing types from %s', self::TYPES_FILE));

        /** @var TypesFile $data */
        $data = JSONL::parse(Storage::path(self::TYPES_FILE));

        $type = ClassResolver::type();

        $upsertData = [];
        foreach ($data as $item) {
            $upsertData[] = [
                'id' => $item['_key'],
                'name' => $item['name']['en'],
                'description' => $item['description']['en'] ?? null,
                'group_id' => $item['groupID'],
                'market_group_id' => $item['marketGroupID'] ?? null,
                'graphic_id' => $item['graphicID'] ?? null,
                'meta_group_id' => $item['metaGroupID'] ?? null,
                'published' => $item['published'] ?? true,
                'mass' => $item['mass'] ?? null,
                'volume' => $item['volume'] ?? null,
                'capacity' => $item['capacity'] ?? null,
                'portion_size' => $item['portionSize'] ?? null,
                'base_price' => $item['basePrice'] ?? null,
                'radius' => $item['radius'] ?? null,
                'icon_id' => $item['iconID'] ?? null,
                'faction_id' => $item['factionID'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        $this->chunkedUpsert(
            $type::query(),
            $upsertData,
            ['id'],
            ['name', 'description', 'group_id', 'market_group_id', 'graphic_id', 'meta_group_id', 'published', 'mass', 'volume', 'capacity', 'portion_size', 'base_price', 'radius', 'icon_id', 'faction_id', 'updated_at'],
            'Upserting types'
        );

        $this->info(sprintf('Successfully seeded %d types', count($data)));

        return self::SUCCESS;
    }
}
