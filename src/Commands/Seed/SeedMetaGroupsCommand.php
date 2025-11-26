<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Commands\Seed;

use Illuminate\Support\Facades\Storage;
use NicolasKion\SDE\ClassResolver;
use NicolasKion\SDE\Data\Dto\MetaGroupDto;
use NicolasKion\SDE\Support\JSONL;
use Throwable;

/**
 * @phpstan-type MetaGroupsFile array{
 *     _key: int,
 *     name: array{en:string|null},
 *     iconID: int|null,
 *     iconSuffix: string|null,
 *     description: array{en:string|null},
 * }[]
 */
class SeedMetaGroupsCommand extends BaseSeedCommand
{
    protected $signature = 'sde:seed:meta-groups';

    /**
     * @throws Throwable
     */
    public function handle(): int
    {
        $this->startMemoryTracking();

        $metaGroup = ClassResolver::metaGroup();

        $count = $this->streamUpsert(
            $metaGroup::query(),
            JSONL::lazy(Storage::path('sde/metaGroups.jsonl'), MetaGroupDto::class),
            function (MetaGroupDto $dto) {
                return [
                    'id' => $dto->id,
                    'name' => $dto->name,
                    'icon_id' => $dto->iconId,
                    'icon_suffix' => $dto->iconSuffix,
                    'description' => $dto->description,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            },
            ['id'],
            ['name', 'icon_id', 'icon_suffix', 'description', 'updated_at'],
            'Seeding Meta Groups'
        );

        $this->displayMemoryStats($count);

        return self::SUCCESS;
    }
}
