<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Commands\Seed;

use Illuminate\Support\Facades\Storage;
use NicolasKion\SDE\ClassResolver;
use NicolasKion\SDE\Data\Dto\AttributeDto;
use NicolasKion\SDE\Support\JSONL;

/**
 * @phpstan-type DogmaAttributeFile array{
 *     _key: int,
 *     attributeCategoryID: int|null,
 *     dataType: int,
 *     highIsGood: null|boolean,
 *     displayName: array{en: string|null},
 *     defaultValue: null|int,
 *     iconID: int|null,
 *     published: bool|null,
 *     name: string|null,
 *     description: string|null,
 *     stackable: boolean|null,
 *     unitID: int|null,
 * }[]
 */
class SeedAttributesCommand extends BaseSeedCommand
{
    protected const string ATTRIBUTES_FILE = 'sde/dogmaAttributes.jsonl';

    protected $signature = 'sde:seed:attributes';

    /**
     * Run the database seeds.
     */
    public function handle(): int
    {
        $this->startMemoryTracking();

        $attribute_class = ClassResolver::attribute();

        $count = $this->streamUpsert(
            $attribute_class::query(),
            JSONL::lazy(Storage::path(self::ATTRIBUTES_FILE), AttributeDto::class),
            function (AttributeDto $dto) {
                return [
                    'id' => $dto->id,
                    'high_is_good' => $dto->highIsGood,
                    'description' => $dto->description,
                    'default_value' => $dto->defaultValue,
                    'icon_id' => $dto->iconId,
                    'published' => $dto->published,
                    'display_name' => $dto->displayName,
                    'name' => $dto->name,
                    'stackable' => $dto->stackable,
                    'unit_id' => $dto->unitId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            },
            ['id'],
            ['high_is_good', 'description', 'default_value', 'icon_id', 'published', 'display_name', 'name', 'stackable', 'unit_id', 'updated_at'],
            'Seeding Attributes'
        );

        $attribute_class::query()->where('display_name', 'rate of fire bonus')->update(['display_name' => 'Rate Of Fire Bonus']);

        $this->displayMemoryStats($count);

        return self::SUCCESS;
    }
}
