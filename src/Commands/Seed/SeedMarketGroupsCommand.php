<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Commands\Seed;

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
class SeedMarketGroupsCommand extends BaseSeedCommand
{
    protected $signature = 'sde:seed:market-groups';

    /**
     * @throws Throwable
     */
    public function handle(): int
    {
        $file_name = 'sde/fsd/marketGroups.yaml';

        $this->info(sprintf('Parsing market groups from %s', $file_name));

        /** @var MarketGroupsFile $data */
        $data = Yaml::parseFile(Storage::path($file_name));

        $marketGroup = ClassResolver::marketGroup();

        $upsertData = [];
        foreach ($data as $key => $item) {
            $upsertData[] = [
                'id' => $key,
                'parent_id' => $item['parentGroupID'] ?? null,
                'name' => $item['nameID']['en'],
                'description' => $item['descriptionID']['en'] ?? null,
                'icon_id' => $item['iconID'] ?? null,
                'has_types' => $item['hasTypes'] ?? true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::transaction(function () use ($upsertData, $marketGroup) {
            Schema::disableForeignKeyConstraints();

            $this->chunkedUpsert(
                $marketGroup::query(),
                $upsertData,
                ['id'],
                ['parent_id', 'name', 'description', 'icon_id', 'has_types', 'updated_at']
            );

            Schema::enableForeignKeyConstraints();
        });
        $this->info(sprintf('Successfully seeded %d market groups', count($data)));

        return self::SUCCESS;
    }
}
