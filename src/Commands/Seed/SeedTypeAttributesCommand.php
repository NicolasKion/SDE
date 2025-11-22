<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Commands\Seed;

use Exception;
use Illuminate\Support\Facades\Storage;
use NicolasKion\SDE\ClassResolver;
use NicolasKion\SDE\Support\JSONL;

/**
 * @phpstan-type DogmaAttributesFile array{
 *     _key: int,
 *     dogmaAttributes: array{
 *         attributeID: int,
 *         value: float,
 *     }[]
 * }[]
 */
class SeedTypeAttributesCommand extends BaseSeedCommand
{
    protected const string TYPE_DOGMA_FILE = 'sde/typeDogma.jsonl';

    protected $signature = 'sde:seed:type-attributes';

    /**
     * @throws Exception
     */
    public function handle(): int
    {
        $this->ensureSDEExists();

        $this->info(sprintf('Parsing type attributes from %s', self::TYPE_DOGMA_FILE));

        /** @var DogmaAttributesFile $data */
        $data = JSONL::parse(Storage::path(self::TYPE_DOGMA_FILE));

        $typeAttribute = ClassResolver::typeAttribute();

        $upsertData = [];
        foreach ($data as $item) {
            $type_id = $item['_key'];
            foreach ($item['dogmaAttributes'] ?? [] as $attribute) {
                $upsertData[] = [
                    'type_id' => $type_id,
                    'attribute_id' => $attribute['attributeID'],
                    'value' => $attribute['value'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        $this->chunkedUpsert(
            $typeAttribute::query(),
            $upsertData,
            ['type_id', 'attribute_id'],
            ['value', 'updated_at'],
            'Upserting type attributes'
        );

        $this->info(sprintf('Successfully seeded %d type attributes', count($upsertData)));

        return self::SUCCESS;
    }
}
