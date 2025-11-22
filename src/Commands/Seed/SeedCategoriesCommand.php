<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Commands\Seed;

use Illuminate\Support\Facades\Storage;
use NicolasKion\SDE\ClassResolver;
use NicolasKion\SDE\Support\JSONL;

/**
 * @phpstan-type CategoryFile array{
 *     _key: int,
 *     name: array{en:string|null},
 *     published: boolean|null,
 * }[]
 */
class SeedCategoriesCommand extends BaseSeedCommand
{
    protected const string CATEGORIES_FILE = 'sde/categories.jsonl';

    protected $signature = 'sde:seed:categories';

    public function handle(): int
    {
        /** @var CategoryFile $data */
        $data = JSONL::parse(Storage::path(self::CATEGORIES_FILE));

        $category = ClassResolver::category();

        $upsertData = [];
        foreach ($data as $item) {
            $upsertData[] = [
                'id' => $item['_key'],
                'name' => $item['name']['en'],
                'published' => $item['published'] ?? true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        $this->chunkedUpsert(
            $category::query(),
            $upsertData,
            ['id'],
            ['name', 'published', 'updated_at']
        );

        $this->info(sprintf('Successfully seeded %d categories', count($data)));

        return self::SUCCESS;
    }
}
