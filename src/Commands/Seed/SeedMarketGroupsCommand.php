<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Commands\Seed;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use NicolasKion\SDE\ClassResolver;
use NicolasKion\SDE\Support\JSONL;
use Throwable;

/**
 * @phpstan-type MarketGroupsFile array{
 *     _key: int,
 *     parentGroupID: int|null,
 *     name: array{en: string|null},
 *     description: array{en: null|string},
 *     iconID: int|null,
 *     hasTypes: boolean|null,
 * }[]
 */
class SeedMarketGroupsCommand extends BaseSeedCommand
{
    public const string MARKET_GROUPS_FILE = 'sde/marketGroups.jsonl';

    protected $signature = 'sde:seed:market-groups';

    /**
     * @throws Throwable
     */
    public function handle(): int
    {

        /** @var MarketGroupsFile $data */
        $data = JSONL::parse(Storage::path(self::MARKET_GROUPS_FILE));

        $marketGroup = ClassResolver::marketGroup();

        $upsertData = [];
        foreach ($data as $item) {
            $upsertData[] = [
                'id' => $item['_key'],
                'parent_id' => $item['parentGroupID'] ?? null,
                'name' => $item['name']['en'] ?? '',
                'description' => $item['description']['en'] ?? '',
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
