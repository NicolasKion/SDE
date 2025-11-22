<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Commands\Seed;

use Illuminate\Support\Facades\Storage;
use NicolasKion\SDE\ClassResolver;
use NicolasKion\SDE\Support\JSONL;

/**
 * @phpstan-type GroupsFile array{
 *     _key: int,
 *     name: array{en: string|null},
 *     categoryID: int,
 *     published: boolean|null,
 *     anchorable: boolean|null,
 *     fittableNonSingleton: boolean|null,
 *     useBasePrice: boolean|null,
 * }[]
 */
class SeedGroupsCommand extends BaseSeedCommand
{
    protected const string GROUPS_FILE = 'sde/groups.jsonl';

    protected $signature = 'sde:seed:groups';

    public function handle(): int
    {
        /** @var GroupsFile $data */
        $data = JSONL::parse(Storage::path(self::GROUPS_FILE));

        $group = ClassResolver::group();

        $upsertData = [];
        foreach ($data as $item) {
            $upsertData[] = [
                'id' => $item['_key'],
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
