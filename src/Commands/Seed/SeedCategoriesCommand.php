<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Commands\Seed;

use Illuminate\Support\Facades\Storage;
use NicolasKion\SDE\ClassResolver;
use NicolasKion\SDE\Data\Dto\CategoryDto;
use NicolasKion\SDE\Support\JSONL;

/**
 * @phpstan-type CategoryFile array{
 *     _key: int,
 *     name: array{en:string|null},
 *     published: boolean|null,
 * }[]
 */
class SeedCategoriesCommand extends BaseSeedCommand
{
    protected const string CATEGORIES_FILE = 'sde/categories.jsonl';

    protected $signature = 'sde:seed:categories';

    public function handle(): int
    {
        $this->startMemoryTracking();

        $category = ClassResolver::category();

        $count = $this->streamUpsert(
            $category::query(),
            JSONL::lazy(Storage::path(self::CATEGORIES_FILE), CategoryDto::class),
            function (CategoryDto $dto) {
                return [
                    'id' => $dto->id,
                    'name' => $dto->name,
                    'published' => $dto->published,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            },
            ['id'],
            ['name', 'published', 'updated_at'],
            'Seeding Categories'
        );

        $this->displayMemoryStats($count);

        return self::SUCCESS;
    }
}
