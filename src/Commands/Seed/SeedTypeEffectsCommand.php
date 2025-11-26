<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Commands\Seed;

use Exception;
use Illuminate\Support\Facades\Storage;
use NicolasKion\SDE\ClassResolver;
use NicolasKion\SDE\Data\Dto\TypeDogmaDto;
use NicolasKion\SDE\Support\JSONL;

use function Laravel\Prompts\spin;

/**
 * @phpstan-type DogmaEffectsFile array{
 *     _key: int,
 *     dogmaEffects: array{
 *         effectID: int,
 *         isDefault: bool,
 *     }[]
 * }[]
 */
class SeedTypeEffectsCommand extends BaseSeedCommand
{
    protected const string TYPE_DOGMA_FILE = 'sde/typeDogma.jsonl';

    protected $signature = 'sde:seed:type-effects';

    /**
     * @throws Exception
     */
    public function handle(): int
    {
        $this->ensureSDEExists();
        $this->startMemoryTracking();

        $count = spin(fn () => $this->processTypeEffects(), 'Seeding Type Effects');

        $this->displayMemoryStats($count);

        return self::SUCCESS;
    }

    /**
     * Process type effects from JSONL file
     */
    private function processTypeEffects(): int
    {
        $typeEffect = ClassResolver::typeEffect();
        $upsertData = [];
        $validIds = [];
        $count = 0;

        // Process type-effect relationships
        // Each type can have multiple dogma effects
        // Generate unique ID based on type_id and effect_id combination
        foreach (JSONL::lazy(Storage::path(self::TYPE_DOGMA_FILE), TypeDogmaDto::class) as $typeDogma) {
            foreach ($typeDogma->dogmaEffects as $effect) {
                // Generate a unique ID from type_id and effect_id
                // Use a simple formula: (type_id * 1000000) + effect_id
                // This ensures uniqueness as long as effect_id < 1000000
                $id = ($typeDogma->typeId * 1000000) + $effect->effectId;
                $validIds[] = $id;

                $upsertData[] = [
                    'id' => $id,
                    'type_id' => $typeDogma->typeId,
                    'effect_id' => $effect->effectId,
                    'is_default' => $effect->isDefault,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $count++;
            }

            // Flush buffer when chunk size is reached
            if (count($upsertData) >= self::UPSERT_CHUNK_SIZE) {
                $typeEffect::upsert(
                    $upsertData,
                    ['id'],
                    ['type_id', 'effect_id', 'is_default', 'updated_at']
                );

                $upsertData = [];
            }
        }

        // Flush remaining effects
        if (! empty($upsertData)) {
            $typeEffect::upsert(
                $upsertData,
                ['id'],
                ['type_id', 'effect_id', 'is_default', 'updated_at']
            );
        }

        // Delete type effects that are no longer in the SDE data
        if (! empty($validIds)) {
            // Get all existing IDs from database
            $existingIds = $typeEffect::query()->pluck('id')->toArray();

            // Calculate which IDs should be deleted (exist in DB but not in SDE data)
            $idsToDelete = array_diff($existingIds, $validIds);

            if (! empty($idsToDelete)) {
                // Delete in chunks to avoid query size limits
                foreach (array_chunk($idsToDelete, 1000) as $chunk) {
                    $typeEffect::query()->whereIn('id', $chunk)->delete();
                }
            }
        }

        return $count;
    }
}
