<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Commands\Seed;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use NicolasKion\SDE\ClassResolver;

/**
 * @phpstan-type UnitsResponse array<int, array{
 *     displayName: string|null,
 *     description: string|null,
 *     name: string|null
 * }>
 */
class SeedUnitsCommand extends BaseSeedCommand
{
    protected const string UNITS_URL = 'https://sde.hoboleaks.space/tq/dogmaunits.json';

    protected $signature = 'sde:seed:units';

    /**
     * @throws ConnectionException
     */
    public function handle(): int
    {
        $this->info(sprintf('Fetching units from %s', self::UNITS_URL));

        $response = Http::get(self::UNITS_URL);

        /** @var UnitsResponse $data */
        $data = $response->json();

        $unit = ClassResolver::unit();

        $upsertData = [];
        foreach ($data as $key => $item) {
            $upsertData[] = [
                'id' => $key,
                'display_name' => $item['displayName'] ?? '',
                'description' => $item['description'] ?? '',
                'name' => $item['name'] ?? '',
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
