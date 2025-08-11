<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Commands\Seed;

use Illuminate\Support\Facades\Storage;
use NicolasKion\SDE\ClassResolver;
use Symfony\Component\Yaml\Yaml;

/**
 * @phpstan-type CategoryFile array<int, array{
 *     name: array{en:string|null},
 *     published: boolean|null,
 * }>
 */
class SeedCategoriesCommand extends BaseSeedCommand
{
    protected $signature = 'sde:seed:categories';

    public function handle(): int
    {
        $file_name = 'sde/fsd/categories.yaml';

        $this->info(sprintf('Parsing categories from %s', $file_name));

        /** @var CategoryFile $data */
        $data = Yaml::parseFile(Storage::path($file_name));

        $category = ClassResolver::category();

        $upsertData = [];
        foreach ($data as $key => $item) {
            $upsertData[] = [
                'id' => $key,
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
