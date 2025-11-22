<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Commands\Seed;

use Exception;
use Illuminate\Support\Facades\Storage;
use NicolasKion\SDE\ClassResolver;
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

        /** @var IconsFile $data */
        $data = JSONL::parse(Storage::path(self::ICONS_FILE));

        $icon = ClassResolver::icon();

        $upsertData = [];
        foreach ($data as $item) {
            $upsertData[] = [
                'id' => $item['_key'],
                'file' => $item['iconFile'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        $this->chunkedUpsert(
            $icon::query(),
            $upsertData,
            ['id'],
            ['file', 'updated_at']
        );

        $this->info(sprintf('Successfully seeded %d icons', count($data)));

        return self::SUCCESS;
    }
}
