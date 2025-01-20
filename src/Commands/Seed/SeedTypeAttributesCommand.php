<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Commands\Seed;

use Exception;
use Illuminate\Support\Facades\Storage;
use NicolasKion\SDE\ClassResolver;
use Symfony\Component\Yaml\Yaml;

/**
 * @phpstan-type DogmaAttributesFile array<int,array{
 *     dogmaAttributes: array<int, array{
 *         attributeID: int,
 *         value: float,
 *     }>
 * }>
 */
class SeedTypeAttributesCommand extends BaseSeedCommand
{
    protected $signature = 'sde:seed:type-attributes';

    /**
     * @throws Exception
     */
    public function handle(): int
    {
        $this->ensureSDEExists();

        $file = 'sde/fsd/typeDogma.yaml';

        /** @var DogmaAttributesFile $data */
        $data = Yaml::parseFile(Storage::path($file));

        $typeAttribute = ClassResolver::typeAttribute();

        foreach ($data as $type_id => $attributes) {
            foreach ($attributes['dogmaAttributes'] as $attribute) {
                $typeAttribute::query()->updateOrInsert(['type_id' => $type_id, 'attribute_id' => $attribute['attributeID']], [
                    'value' => $attribute['value'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $this->info(sprintf('Successfully seeded %d type attributes', count($data)));

        return self::SUCCESS;
    }
}
