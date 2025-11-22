<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Commands\Seed;

use Illuminate\Support\Facades\Storage;
use NicolasKion\SDE\ClassResolver;
use NicolasKion\SDE\Support\JSONL;

/**
 * @phpstan-type UnitsFile array<int, array{
 *     _key: int,
 *     name: string,
 *     description: array{en: string}|null,
 *     displayName: array{en: string}|null,
 * }>
 */
class SeedUnitsCommand extends BaseSeedCommand
{
    protected const string UNITS_FILE = 'sde/dogmaUnits.jsonl';

    protected $signature = 'sde:seed:units';

    public function handle(): int
    {
        /** @var UnitsFile $data */
        $data = JSONL::parse(Storage::path(self::UNITS_FILE));

        $unit = ClassResolver::unit();

        $upsertData = [];
        foreach ($data as $item) {
            $upsertData[] = [
                'id' => $item['_key'],
                'name' => $item['name'],
                'description' => $item['description']['en'] ?? '',
                'display_name' => $item['displayName']['en'] ?? '',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        $this->chunkedUpsert(
            $unit::query(),
            $upsertData,
            ['id'],
            ['display_name', 'description', 'name', 'updated_at']
        );

        $this->info(sprintf('Successfully seeded %d units', count($data)));

        return self::SUCCESS;
    }
}
