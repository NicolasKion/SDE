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

        /** @var IconsFile $data */
        $data = Yaml::parseFile(Storage::path($icon_file));

        $icon = ClassResolver::icon();

        foreach ($data as $id => $values) {
            $icon::query()->updateOrInsert(['id' => $id], [
                'file' => $values['iconFile'],
                'description' => $values['description'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->info(sprintf('Successfully seeded %d icons', count($data)));

        return self::SUCCESS;
    }
}
