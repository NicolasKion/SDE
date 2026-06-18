<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Commands\Seed;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use NicolasKion\SDE\Data\Dto\DynamicItemAttributeDto;
use NicolasKion\SDE\Models\Mutaplasmid;
use NicolasKion\SDE\Models\MutaplasmidApplicableType;
use NicolasKion\SDE\Models\MutaplasmidAttribute;
use NicolasKion\SDE\Support\JSONL;
use Throwable;

use function Laravel\Prompts\spin;

class SeedDynamicItemAttributesCommand extends BaseSeedCommand
{
    protected $signature = 'sde:seed:dynamic-items';

    /**
     * @throws Throwable
     */
    public function handle(): int
    {
        $this->startMemoryTracking();

        $count = spin(function () {
            $path = Storage::path('sde/dynamicItemAttributes.jsonl');
            $count = 0;

            $mutaplasmids = [];
            $applicableTypes = [];
            $attributes = [];

            foreach (JSONL::lazy($path, DynamicItemAttributeDto::class) as $dto) {
                foreach ($dto->inputOutputMapping as $mapping) {
                    $mutaplasmids[] = [
                        'id' => $dto->id,
                        'output_type_id' => $mapping['resultingType'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    foreach ($mapping['applicableTypes'] as $inputTypeId) {
                        $applicableTypes[] = [
                            'mutaplasmid_id' => $dto->id,
                            'input_type_id' => $inputTypeId,
                        ];
                    }
                }

                foreach ($dto->inputOutputMapping[0]['applicableTypes'] ?? [] as $_) {
                    // Attributes are per-mutaplasmid, not per-mapping
                    break;
                }

                foreach ($data = (array) $dto as $key => $value) {
                    // skip non-attribute fields
                }

                $count++;
            }

            // Re-read for attributes properly
            $attributes = [];
            foreach (JSONL::lazy($path, DynamicItemAttributeDto::class) as $dto) {
                $rawLines = file($path);
                $raw = json_decode(
                    collect($rawLines === false ? [] : $rawLines)->skip(0)->first(fn ($line) => str_contains($line, '"_key": '.$dto->id)) ?? '{}',
                    true
                );

                // Actually, let me use the raw data approach
                break;
            }

            // Simpler: re-read the file line by line
            $mutaplasmids = [];
            $applicableTypes = [];
            $attributes = [];

            $lines = file($path);
            if ($lines === false) {
                $lines = [];
            }

            foreach ($lines as $line) {
                $entry = json_decode(trim($line), true);
                if (! $entry || ! is_array($entry)) {
                    continue;
                }

                /** @var array{_key: int, inputOutputMapping?: array<int, array{applicableTypes: int[], resultingType: int}>, attributeIDs?: array<int, array{_key: int, min: float|int, max: float|int, highIsGood?: bool}>} $entry */
                $mutaplasmidId = $entry['_key'];

                foreach ($entry['inputOutputMapping'] ?? [] as $mapping) {
                    $mutaplasmids[$mutaplasmidId] = [
                        'id' => $mutaplasmidId,
                        'output_type_id' => $mapping['resultingType'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    foreach ($mapping['applicableTypes'] as $inputTypeId) {
                        $applicableTypes[] = [
                            'mutaplasmid_id' => $mutaplasmidId,
                            'input_type_id' => $inputTypeId,
                        ];
                    }
                }

                foreach ($entry['attributeIDs'] ?? [] as $attr) {
                    $attributes[] = [
                        'mutaplasmid_id' => $mutaplasmidId,
                        'attribute_id' => $attr['_key'],
                        'min' => $attr['min'],
                        'max' => $attr['max'],
                        'high_is_good' => $attr['highIsGood'] ?? true,
                    ];
                }
            }

            DB::transaction(function () use ($mutaplasmids, $applicableTypes, $attributes) {
                MutaplasmidAttribute::query()->delete();
                MutaplasmidApplicableType::query()->delete();
                Mutaplasmid::query()->delete();

                foreach (array_chunk(array_values($mutaplasmids), self::UPSERT_CHUNK_SIZE) as $chunk) {
                    Mutaplasmid::query()->upsert($chunk, ['id'], ['output_type_id', 'updated_at']);
                }

                foreach (array_chunk($applicableTypes, self::UPSERT_CHUNK_SIZE) as $chunk) {
                    MutaplasmidApplicableType::query()->insert($chunk);
                }

                foreach (array_chunk($attributes, self::UPSERT_CHUNK_SIZE) as $chunk) {
                    MutaplasmidAttribute::query()->insert($chunk);
                }
            });

            return count($mutaplasmids);
        }, 'Seeding Dynamic Item Attributes');

        $this->displayMemoryStats($count);

        return self::SUCCESS;
    }
}
