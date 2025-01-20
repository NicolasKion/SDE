<?php

namespace NicolasKion\SDE\Commands\Seed;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use NicolasKion\SDE\ClassResolver;

class SeedUnitsCommand extends Command
{
    protected const UNITS_URL = 'https://sde.hoboleaks.space/tq/dogmaunits.json';
    protected $signature = 'sde:seed:units';

    public function handle(): int
    {
        $response = Http::get(self::UNITS_URL);

        /** @var array<int, array{displayName: string|null, description: string|null, name: string|null}> $data */
        $data = $response->json();

        $unit = ClassResolver::unit();

        foreach ($data as $id => $values) {
            $name = $values['name'] ?? '';
            $display_name = $values['displayName'] ?? '';
            $description = $values['description'] ?? '';

            $unit::query()->updateOrInsert([
                'id' => $id,
            ], [
                    'id' => $id,
                    'display_name' => $display_name,
                    'description' => $description,
                    'name' => $name,
                ]
            );
        }

        $this->info(sprintf('Successfully seeded %d units', count($data)));

        return self::SUCCESS;
    }
}
