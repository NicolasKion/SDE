<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Commands\Seed;

use Illuminate\Support\Facades\Storage;
use NicolasKion\SDE\ClassResolver;
use NicolasKion\SDE\Data\Dto\GroupDto;
use NicolasKion\SDE\Support\JSONL;

/**
 * @phpstan-type GroupsFile array{
 *     _key: int,
 *     name: array{en: string|null},
 *     categoryID: int,
 *     published: boolean|null,
 *     anchorable: boolean|null,
 *     fittableNonSingleton: boolean|null,
 *     useBasePrice: boolean|null,
 * }[]
 */
class SeedGroupsCommand extends BaseSeedCommand
{
    protected const string GROUPS_FILE = 'sde/groups.jsonl';

    protected $signature = 'sde:seed:groups';

    public function handle(): int
    {
        $this->startMemoryTracking();

        $group = ClassResolver::group();

        $count = $this->streamUpsert(
            $group::query(),
            JSONL::lazy(Storage::path(self::GROUPS_FILE), GroupDto::class),
            function (GroupDto $dto) {
                return [
                    'id' => $dto->id,
                    'name' => $dto->name,
                    'category_id' => $dto->categoryId,
                    'published' => $dto->published,
                    'anchorable' => $dto->anchorable,
                    'fittable_non_singleton' => $dto->fittableNonSingleton,
                    'use_base_price' => $dto->useBasePrice,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            },
            ['id'],
            ['name', 'category_id', 'published', 'anchorable', 'fittable_non_singleton', 'use_base_price', 'updated_at'],
            'Seeding Groups'
        );

        $this->displayMemoryStats($count);

        return self::SUCCESS;
    }
}
