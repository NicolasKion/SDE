<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Commands\Seed;

use Exception;
use Illuminate\Support\Facades\Storage;
use NicolasKion\SDE\ClassResolver;
use NicolasKion\SDE\Data\Dto\GraphicDto;
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

    /**
     * @throws Exception
     */
    public function handle(): int
    {
        $this->ensureSDEExists();
        $this->startMemoryTracking();

        $graphic = ClassResolver::graphic();

        $count = $this->streamUpsert(
            $graphic::query(),
            JSONL::lazy(Storage::path(self::GRAPHICS_FILE), GraphicDto::class),
            function (GraphicDto $dto) {
                return [
                    'id' => $dto->id,
                    'file' => $dto->file,
                    'sof_faction_name' => $dto->sofFactionName,
                    'sof_hull_name' => $dto->sofHullName,
                    'sof_race_name' => $dto->sofRaceName,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            },
            ['id'],
            ['file', 'sof_faction_name', 'sof_hull_name', 'sof_race_name', 'updated_at'],
            'Seeding Graphics'
        );

        $this->displayMemoryStats($count);

        return self::SUCCESS;
    }
}
