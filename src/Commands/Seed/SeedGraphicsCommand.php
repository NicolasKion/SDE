<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Commands\Seed;

use Illuminate\Support\Facades\Storage;
use NicolasKion\SDE\ClassResolver;
use NicolasKion\SDE\Support\JSONL;

/**
 * @phpstan-type GraphicsFile array{
 *     _key: int,
 *     iconFolder: string|null,
 *     sofFactionName: string|null,
 *     sofHullName: string|null,
 *     sofRaceName: string|null,
 * }[]
 */
class SeedGraphicsCommand extends BaseSeedCommand
{
    protected const string GRAPHICS_FILE = 'sde/graphics.jsonl';

    protected $signature = 'sde:seed:graphics';

    public function handle(): int
    {
        /** @var GraphicsFile $data */
        $data = JSONL::parse(Storage::path(self::GRAPHICS_FILE));

        $graphic = ClassResolver::graphic();

        $upsertData = [];
        foreach ($data as $item) {
            $upsertData[] = [
                'id' => $item['_key'],
                'file' => $item['iconFolder'] ?? null,
                'sof_faction_name' => $item['sofFactionName'] ?? null,
                'sof_hull_name' => $item['sofHullName'] ?? null,
                'sof_race_name' => $item['sofRaceName'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        $this->chunkedUpsert(
            $graphic::query(),
            $upsertData,
            ['id'],
            ['file', 'sof_faction_name', 'sof_hull_name', 'sof_race_name', 'updated_at']
        );

        $this->info(sprintf('Successfully seeded %d graphics', count($data)));

        return self::SUCCESS;
    }
}
