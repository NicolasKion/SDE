<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Commands\Seed;

use Illuminate\Support\Facades\Storage;
use NicolasKion\SDE\ClassResolver;
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
        /** @var DogmaAttributeFile $data */
        $data = JSONL::parse(Storage::path(self::ATTRIBUTES_FILE));

        $attribute_class = ClassResolver::attribute();

        $upsertData = [];
        foreach ($data as $item) {
            $upsertData[] = [
                'id' => $item['_key'],
                'high_is_good' => $item['highIsGood'] ?? false,
                'description' => $item['description'] ?? '',
                'default_value' => $item['defaultValue'] ?? 0,
                'icon_id' => $item['iconID'] ?? null,
                'published' => $item['published'] ?? false,
                'display_name' => $item['displayName']['en'] ?? '',
                'name' => $item['name'] ?? '',
                'stackable' => $item['stackable'] ?? false,
                'unit_id' => $item['unitID'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        $this->chunkedUpsert(
            $attribute_class::query(),
            $upsertData,
            ['id'],
            ['high_is_good', 'description', 'default_value', 'icon_id', 'published', 'display_name', 'name', 'stackable', 'unit_id', 'updated_at']
        );

        $attribute_class::query()->where('display_name', 'rate of fire bonus')->update(['display_name' => 'Rate Of Fire Bonus']);

        $this->info(sprintf('Successfully seeded %d attributes', count($data)));

        return self::SUCCESS;
    }
}
