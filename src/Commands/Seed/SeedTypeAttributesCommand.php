<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Commands\Seed;

use Exception;
use Generator;
use Illuminate\Support\Facades\Storage;
use NicolasKion\SDE\ClassResolver;
use RuntimeException;

use function Laravel\Prompts\spin;

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
        $query = $typeAttribute::query();

        /** @var list<array<string, mixed>> $upsertData */
        $upsertData = [];
        $count = 0;

        // Process type-attribute relationships
        // Each type can have multiple dogma attributes
        foreach ($this->streamDogmaLines(Storage::path(self::TYPE_DOGMA_FILE)) as $item) {
            $type_id = $item['_key'] ?? null;
            $dogmaAttributes = $item['dogmaAttributes'] ?? [];

            if (! is_int($type_id) || ! is_array($dogmaAttributes)) {
                continue;
            }

            foreach ($dogmaAttributes as $attribute) {
                if (! is_array($attribute)) {
                    continue;
                }

                $attributeId = $attribute['attributeID'] ?? null;
                $value = $attribute['value'] ?? null;

                if (! is_int($attributeId) || ! (is_int($value) || is_float($value))) {
                    continue;
                }

                $upsertData[] = [
                    'type_id' => $type_id,
                    'attribute_id' => $attributeId,
                    'value' => $value,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $count++;
            }

            // Flush buffer when chunk size is reached
            if (count($upsertData) >= self::UPSERT_CHUNK_SIZE) {
                $query->upsert(
                    $upsertData,
                    ['type_id', 'attribute_id'],
                    ['value', 'updated_at']
                );

                $upsertData = [];
            }
        }

        // Flush remaining attributes
        if ($upsertData !== []) {
            $query->upsert(
                $upsertData,
                ['type_id', 'attribute_id'],
                ['value', 'updated_at']
            );
        }

        return $count;
    }

    /**
     * Lazily stream decoded JSONL lines from the type dogma file.
     *
     * Each line is a JSON object decoded into a string-keyed array. The shape
     * is only guaranteed by the external SDE data, so callers must guard the
     * offsets they read.
     *
     * @return Generator<int, array<string, mixed>>
     */
    private function streamDogmaLines(string $file): Generator
    {
        $handle = fopen($file, 'r');

        if ($handle === false) {
            throw new RuntimeException("Could not open file: $file");
        }

        try {
            while (($line = fgets($handle)) !== false) {
                $line = trim($line);
                if ($line === '') {
                    continue;
                }

                $decoded = json_decode($line, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new RuntimeException('JSON decode error: '.json_last_error_msg());
                }

                if (is_array($decoded)) {
                    /** @var array<string, mixed> $decoded */
                    yield $decoded;
                }
            }
        } finally {
            fclose($handle);
        }
    }
}
