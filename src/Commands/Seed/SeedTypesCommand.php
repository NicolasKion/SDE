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

        /** @var TypesFile $data */
        $data = Yaml::parseFile(Storage::path($file));

        $type = ClassResolver::type();

        foreach ($data as $id => $values) {
            $type::query()->updateOrCreate(['id' => $id], [
                'id' => $id,
                'name' => $values['name']['en'],
                'description' => $values['description']['en'] ?? null,
                'group_id' => $values['groupID'],
                'market_group_id' => $values['marketGroupID'] ?? null,
                'graphic_id' => $values['graphicID'] ?? null,
                'meta_group_id' => $values['metaGroupID'] ?? null,
                'published' => $values['published'] ?? true,
                'mass' => $values['mass'] ?? null,
                'volume' => $values['volume'] ?? null,
                'capacity' => $values['capacity'] ?? null,
                'portion_size' => $values['portionSize'] ?? null,
                'base_price' => $values['basePrice'] ?? null,
                'radius' => $values['radius'] ?? null,
                'icon_id' => $values['iconID'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->info(sprintf('Successfully seeded %d types', count($data)));

        return self::SUCCESS;
    }
}
