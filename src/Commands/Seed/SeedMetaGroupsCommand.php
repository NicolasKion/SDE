<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Commands\Seed;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use NicolasKion\SDE\ClassResolver;
use Symfony\Component\Yaml\Yaml;

/**
 * @phpstan-type MetaGroupsFile array<int, array{
 *     nameID: array{en:string|null},
 *     iconID: int|null,
 *     iconSuffix: string|null,
 *     descriptionID: array{en:string|null},
 * }>
 */
class SeedMetaGroupsCommand extends Command
{
    protected $signature = 'sde:seed:meta-groups';

    public function handle(): int
    {
        $file_name = 'sde/fsd/metaGroups.yaml';

        /** @var MetaGroupsFile $data */
        $data = Yaml::parseFile(Storage::path($file_name));

        $metaGroup = ClassResolver::metaGroup();

        DB::transaction(function () use ($data, $metaGroup) {
            foreach ($data as $id => $values) {
                $metaGroup::query()->updateOrInsert(['id' => $id], [
                    'name' => $values['nameID']['en'],
                    'icon_id' => $values['iconID'] ?? null,
                    'icon_suffix' => $values['iconSuffix'] ?? null,
                    'description' => $values['descriptionID']['en'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        $this->info(sprintf('Successfully seeded %d meta groups', count($data)));

        return self::SUCCESS;
    }
}
