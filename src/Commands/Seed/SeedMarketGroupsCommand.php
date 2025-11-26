<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Commands\Seed;

use Illuminate\Support\Facades\Storage;
use NicolasKion\SDE\ClassResolver;
use NicolasKion\SDE\Data\Dto\MarketGroupDto;
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
        $this->startMemoryTracking();

        $marketGroup = ClassResolver::marketGroup();

        $count = $this->streamUpsert(
            $marketGroup::query(),
            JSONL::lazy(Storage::path(self::MARKET_GROUPS_FILE), MarketGroupDto::class),
            function (MarketGroupDto $dto) {
                return [
                    'id' => $dto->id,
                    'parent_id' => $dto->parentId,
                    'name' => $dto->name,
                    'description' => $dto->description,
                    'icon_id' => $dto->iconId,
                    'has_types' => $dto->hasTypes,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            },
            ['id'],
            ['parent_id', 'name', 'description', 'icon_id', 'has_types', 'updated_at'],
            'Seeding Market Groups'
        );

        $this->displayMemoryStats($count);

        return self::SUCCESS;
    }
}
