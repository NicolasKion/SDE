<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Commands\Seed;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use NicolasKion\SDE\ClassResolver;
use Symfony\Component\Yaml\Yaml;

/**
 * @phpstan-type RacesFile array<int,array{
 *     nameID: array{en:string|null},
 *     descriptionID: array{en: string|null},
 *     iconID: int|null
 * }>
 */
class SeedRacesCommand extends Command
{
    protected $signature = 'sde:seed:races';

    public function handle(): int
    {
        $file_name = 'sde/fsd/races.yaml';

        /** @var RacesFile $data */
        $data = Yaml::parseFile(Storage::path($file_name));

        $race = ClassResolver::race();

        foreach ($data as $id => $values) {
            $race::query()->updateOrInsert(['id' => $id], [
                'name' => $values['nameID']['en'],
                'description' => $values['descriptionID']['en'] ?? null,
                'icon_id' => $values['iconID'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->info(sprintf('Successfully seeded %d races', count($data)));

        return self::SUCCESS;
    }
}
