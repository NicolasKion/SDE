<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Commands\Seed;

use Illuminate\Support\Facades\Storage;
use NicolasKion\SDE\ClassResolver;
use Symfony\Component\Yaml\Yaml;

/**
 * @phpstan-type DogmaAttributeFile array<int,array{
 *     highIsGood: null|boolean,
 *     displayNameID: array{en: string|null},
 *     defaultValue: null|int,
 *     iconID: int|null,
 *     published: bool|null,
 *     name: string|null,
 *     description: string|null,
 *     stackable: boolean|null,
 *     unitID: int|null,
 * }>
 */
class SeedAttributesCommand extends BaseSeedCommand
{
    protected $signature = 'sde:seed:attributes';

    /**
     * Run the database seeds.
     */
    public function handle(): int
    {
        $file_name = 'sde/fsd/dogmaAttributes.yaml';

        $this->info(sprintf('Parsing attributes from %s', $file_name));

        /** @var DogmaAttributeFile $data */
        $data = Yaml::parseFile(Storage::path($file_name));

        $attribute_class = ClassResolver::attribute();

        $upsertData = [];
        foreach ($data as $key => $item) {
            $upsertData[] = [
                'id' => $key,
                'high_is_good' => $item['highIsGood'] ?? false,
                'description' => $item['description'] ?? '',
                'default_value' => $item['defaultValue'] ?? 0,
                'icon_id' => $item['iconID'] ?? null,
                'published' => $item['published'] ?? false,
                'display_name' => $item['displayNameID']['en'] ?? '',
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
