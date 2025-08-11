<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Commands\Seed;

use Illuminate\Support\Facades\Storage;
use NicolasKion\SDE\ClassResolver;
use Symfony\Component\Yaml\Yaml;

/**
 * @phpstan-type GraphicsFile array<int, array{
 *     iconInfo: array{folder: null|string},
 *     sofFactionName: string|null,
 *     sofHullName: string|null,
 *     sofRaceName: string|null,
 * }>
 */
class SeedGraphicsCommand extends BaseSeedCommand
{
    protected $signature = 'sde:seed:graphics';

    public function handle(): int
    {
        $file_name = 'sde/fsd/graphicIDs.yaml';

        $this->info(sprintf('Parsing graphics from %s', $file_name));

        /** @var GraphicsFile $data */
        $data = Yaml::parseFile(Storage::path($file_name));

        $graphic = ClassResolver::graphic();

        $upsertData = [];
        foreach ($data as $key => $item) {
            $upsertData[] = [
                'id' => $key,
                'file' => $item['iconInfo']['folder'] ?? null,
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
