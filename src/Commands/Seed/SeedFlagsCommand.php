<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Commands\Seed;

use Illuminate\Support\Facades\Storage;
use NicolasKion\SDE\ClassResolver;
use Symfony\Component\Yaml\Yaml;

/**
 * @phpstan-type FlagsFile array<int, array{
 *     flagID: int,
 *     flagName: string,
 *     flagText: string|null,
 *     orderID: int,
 * }>
 */
class SeedFlagsCommand extends BaseSeedCommand
{
    protected $signature = 'sde:seed:flags';

    public function handle(): int
    {
        $file_name = 'sde/bsd/invFlags.yaml';

        $this->info(sprintf('Parsing flags from %s', $file_name));

        /** @var FlagsFile $data */
        $data = Yaml::parseFile(Storage::path($file_name));

        $flag = ClassResolver::flag();

        $upsertData = [];
        foreach ($data as $item) {
            $upsertData[] = [
                'id' => $item['flagID'],
                'name' => $item['flagName'],
                'text' => $item['flagText'] ?? '',
                'order_id' => $item['orderID'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        $this->chunkedUpsert(
            $flag::query(),
            $upsertData,
            ['id'],
            ['name', 'text', 'order_id', 'updated_at']
        );

        $this->info(sprintf('Successfully seeded %d flags', count($data)));

        return self::SUCCESS;
    }
}
