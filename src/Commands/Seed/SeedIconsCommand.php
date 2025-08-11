<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Commands\Seed;

use Exception;
use Illuminate\Support\Facades\Storage;
use NicolasKion\SDE\ClassResolver;
use Symfony\Component\Yaml\Yaml;

/**
 * @phpstan-type IconsFile array<int, array{
 *     iconFile: string,
 *     description: string|null,
 * }>
 */
class SeedIconsCommand extends BaseSeedCommand
{
    protected $signature = 'sde:seed:icons';

    /**
     * @throws Exception
     */
    public function handle(): int
    {
        $this->ensureSDEExists();

        $icon_file = 'sde/fsd/iconIDs.yaml';

        $this->info(sprintf('Parsing icons from %s', $icon_file));

        /** @var IconsFile $data */
        $data = Yaml::parseFile(Storage::path($icon_file));

        $icon = ClassResolver::icon();

        $upsertData = [];
        foreach ($data as $key => $item) {
            $upsertData[] = [
                'id' => $key,
                'file' => $item['iconFile'],
                'description' => $item['description'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        $this->chunkedUpsert(
            $icon::query(),
            $upsertData,
            ['id'],
            ['file', 'description', 'updated_at']
        );

        $this->info(sprintf('Successfully seeded %d icons', count($data)));

        return self::SUCCESS;
    }
}
