<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Commands\Seed;

use Illuminate\Support\Facades\Storage;
use NicolasKion\SDE\ClassResolver;
use NicolasKion\SDE\Support\JSONL;

/**
 * @phpstan-type RacesFile array{
 *     _key: int,
 *     name: array{en:string|null},
 *     description: array{en: string|null},
 *     iconID: int|null
 * }[]
 */
class SeedRacesCommand extends BaseSeedCommand
{
    protected const string RACES_FILE = 'sde/races.jsonl';

    protected $signature = 'sde:seed:races';

    public function handle(): int
    {
        $this->info(sprintf('Parsing races from %s', self::RACES_FILE));

        /** @var RacesFile $data */
        $data = JSONL::parse(Storage::path(self::RACES_FILE));

        $race = ClassResolver::race();

        $upsertData = [];
        foreach ($data as $item) {
            $upsertData[] = [
                'id' => $item['_key'],
                'name' => $item['name']['en'],
                'description' => $item['description']['en'] ?? null,
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
