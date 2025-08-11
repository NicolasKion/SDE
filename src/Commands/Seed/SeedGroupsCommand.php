<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Commands\Seed;

use Illuminate\Support\Facades\Storage;
use NicolasKion\SDE\ClassResolver;
use Symfony\Component\Yaml\Yaml;

/**
 * @phpstan-type GroupsFile array<int, array{
 *     name: array{en: string|null},
 *     categoryID: int,
 *     published: boolean|null,
 *     anchorable: boolean|null,
 *     fittableNonSingleton: boolean|null,
 *     useBasePrice: boolean|null,
 * }>
 */
class SeedGroupsCommand extends BaseSeedCommand
{
    protected $signature = 'sde:seed:groups';

    public function handle(): int
    {
        $file_name = 'sde/fsd/groups.yaml';

        $this->info(sprintf('Parsing groups from %s', $file_name));

        /** @var GroupsFile $data */
        $data = Yaml::parseFile(Storage::path($file_name));

        $group = ClassResolver::group();

        $upsertData = [];
        foreach ($data as $key => $item) {
            $upsertData[] = [
                'id' => $key,
                'name' => $item['name']['en'],
                'category_id' => $item['categoryID'],
                'published' => $item['published'] ?? true,
                'anchorable' => $item['anchorable'] ?? false,
                'fittable_non_singleton' => $item['fittableNonSingleton'] ?? false,
                'use_base_price' => $item['useBasePrice'] ?? false,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        $this->chunkedUpsert(
            $group::query(),
            $upsertData,
            ['id'],
            ['name', 'category_id', 'published', 'anchorable', 'fittable_non_singleton', 'use_base_price', 'updated_at']
        );

        $this->info(sprintf('Successfully seeded %d groups', count($data)));

        return self::SUCCESS;
    }
}
