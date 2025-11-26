<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Commands\Seed;

use Exception;
use Illuminate\Support\Facades\Storage;
use NicolasKion\SDE\ClassResolver;
use NicolasKion\SDE\Support\JSONL;

use function Laravel\Prompts\spin;

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
        $this->startMemoryTracking();

        $count = spin(fn () => $this->processTypeAttributes(), 'Seeding Type Attributes');

        $this->displayMemoryStats($count);

        return self::SUCCESS;
    }

    /**
     * Process type attributes from JSONL file
     */
    private function processTypeAttributes(): int
    {
        $typeAttribute = ClassResolver::typeAttribute();
        $upsertData = [];
        $count = 0;

        // Process type-attribute relationships
        // Each type can have multiple dogma attributes
        foreach (JSONL::lazy(Storage::path(self::TYPE_DOGMA_FILE)) as $item) {
            $type_id = $item['_key'];
            foreach ($item['dogmaAttributes'] ?? [] as $attribute) {
                $upsertData[] = [
                    'type_id' => $type_id,
                    'attribute_id' => $attribute['attributeID'],
                    'value' => $attribute['value'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $count++;
            }

            // Flush buffer when chunk size is reached
            if (count($upsertData) >= self::UPSERT_CHUNK_SIZE) {
                $typeAttribute::upsert(
                    $upsertData,
                    ['type_id', 'attribute_id'],
                    ['value', 'updated_at']
                );

                $upsertData = [];
            }
        }

        // Flush remaining attributes
        if (! empty($upsertData)) {
            $typeAttribute::upsert(
                $upsertData,
                ['type_id', 'attribute_id'],
                ['value', 'updated_at']
            );
        }

        return $count;
    }
}
