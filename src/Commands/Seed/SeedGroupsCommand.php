<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Commands\Seed;

use Illuminate\Console\Command;
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
class SeedGroupsCommand extends Command
{
    protected $signature = 'sde:seed:groups';

    public function handle(): int
    {
        $file_name = 'sde/fsd/groups.yaml';

        /** @var GroupsFile $data */
        $data = Yaml::parseFile(Storage::path($file_name));

        $group = ClassResolver::group();

        foreach ($data as $id => $values) {
            $group::query()->updateOrInsert(['id' => $id], [
                'name' => $values['name']['en'],
                'category_id' => $values['categoryID'],
                'published' => $values['published'] ?? true,
                'anchorable' => $values['anchorable'] ?? false,
                'fittable_non_singleton' => $values['fittableNonSingleton'] ?? false,
                'use_base_price' => $values['useBasePrice'] ?? false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->info(sprintf('Successfully seeded %d groups', count($data)));

        return self::SUCCESS;
    }
}
