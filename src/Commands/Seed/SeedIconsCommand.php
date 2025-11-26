<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Commands\Seed;

use Exception;
use Illuminate\Support\Facades\Storage;
use NicolasKion\SDE\ClassResolver;
use NicolasKion\SDE\Data\Dto\IconDto;
use NicolasKion\SDE\Support\JSONL;

/**
 * @phpstan-type IconsFile array{
 *     _key: int,
 *     iconFile: string,
 * }[]
 */
class SeedIconsCommand extends BaseSeedCommand
{
    protected const string ICONS_FILE = 'sde/icons.jsonl';

    protected $signature = 'sde:seed:icons';

    /**
     * @throws Exception
     */
    public function handle(): int
    {
        $this->ensureSDEExists();
        $this->startMemoryTracking();

        $icon = ClassResolver::icon();

        $count = $this->streamUpsert(
            $icon::query(),
            JSONL::lazy(Storage::path(self::ICONS_FILE), IconDto::class),
            function (IconDto $dto) {
                return [
                    'id' => $dto->id,
                    'file' => $dto->file,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            },
            ['id'],
            ['file', 'updated_at'],
            'Seeding Icons'
        );

        $this->displayMemoryStats($count);

        return self::SUCCESS;
    }
}
