<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Commands\Seed;

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
class SeedRacesCommand extends BaseSeedCommand
{
    protected $signature = 'sde:seed:races';

    public function handle(): int
    {
        $file_name = 'sde/fsd/races.yaml';

        $this->info(sprintf('Parsing races from %s', $file_name));

        /** @var RacesFile $data */
        $data = Yaml::parseFile(Storage::path($file_name));

        $race = ClassResolver::race();

        $upsertData = [];
        foreach ($data as $key => $item) {
            $upsertData[] = [
                'id' => $key,
                'name' => $item['nameID']['en'],
                'description' => $item['descriptionID']['en'] ?? null,
                'icon_id' => $item['iconID'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        $this->chunkedUpsert(
            $race::query(),
            $upsertData,
            ['id'],
            ['name', 'description', 'icon_id', 'updated_at']
        );

        $this->info(sprintf('Successfully seeded %d races', count($data)));

        return self::SUCCESS;
    }
}
