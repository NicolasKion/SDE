<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Commands\Seed;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use NicolasKion\SDE\ClassResolver;
use Symfony\Component\Yaml\Yaml;
use Throwable;

/**
 * @phpstan-type MarketGroupsFile array<int, array{
 *     parentGroupID: int|null,
 *     nameID: array{en: string|null},
 *     descriptionID: array{en: null|string},
 *     iconID: int|null,
 *     hasTypes: boolean|null,
 * }>
 */
class SeedMarketGroupsCommand extends Command
{
    protected $signature = 'sde:seed:market-groups';

    /**
     * @throws Throwable
     */
    public function handle(): int
    {
        $file_name = 'sde/fsd/marketGroups.yaml';

        /** @var MarketGroupsFile $data */
        $data = Yaml::parseFile(Storage::path($file_name));

        $marketGroup = ClassResolver::marketGroup();

        DB::transaction(function () use ($data, $marketGroup) {
            Schema::disableForeignKeyConstraints();

            foreach ($data as $id => $values) {
                $marketGroup::query()->updateOrInsert(['id' => $id], [
                    'parent_id' => $values['parentGroupID'] ?? null,
                    'name' => $values['nameID']['en'],
                    'description' => $values['descriptionID']['en'] ?? null,
                    'icon_id' => $values['iconID'] ?? null,
                    'has_types' => $values['hasTypes'] ?? true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            Schema::enableForeignKeyConstraints();

        });
        $this->info(sprintf('Successfully seeded %d market groups', count($data)));

        return self::SUCCESS;
    }
}
