<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Commands\Seed;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use NicolasKion\SDE\ClassResolver;
use Symfony\Component\Yaml\Yaml;

/**
 * @phpstan-type CategoryFile array<int, array{
 *     name: array{en:string|null},
 *     published: boolean|null,
 * }>
 */
class SeedCategoriesCommand extends Command
{
    protected $signature = 'sde:seed:categories';

    public function handle(): int
    {
        $file_name = 'sde/fsd/categories.yaml';

        /** @var CategoryFile $data */
        $data = Yaml::parseFile(Storage::path($file_name));

        $category = ClassResolver::category();

        foreach ($data as $id => $values) {
            $category::query()->updateOrInsert(['id' => $id], [
                'name' => $values['name']['en'],
                'published' => $values['published'] ?? true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->info(sprintf('Successfully seeded %d categories', count($data)));

        return self::SUCCESS;
    }
}
