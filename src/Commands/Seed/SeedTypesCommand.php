<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Commands\Seed;

use Exception;
use Illuminate\Support\Facades\Storage;
use NicolasKion\SDE\ClassResolver;
use Symfony\Component\Yaml\Yaml;

/**
 * @phpstan-type TypesFile array<int, array{
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
 * }>
 */
class SeedTypesCommand extends BaseSeedCommand
{
    protected $signature = 'sde:seed:types';

    /**
     * @throws Exception
     */
    public function handle(): int
    {
        $this->ensureSDEExists();

        $file = 'sde/fsd/types.yaml';

        $this->info(sprintf('Parsing types from %s', $file));

        /** @var TypesFile $data */
        $data = Yaml::parseFile(Storage::path($file));

        $type = ClassResolver::type();

        $upsertData = [];
        foreach ($data as $key => $item) {
            $upsertData[] = [
                'id' => $key,
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
