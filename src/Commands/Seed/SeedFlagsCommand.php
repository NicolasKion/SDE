<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Commands\Seed;

use Illuminate\Console\Command;
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
class SeedFlagsCommand extends Command
{
    protected $signature = 'sde:seed:flags';

    public function handle(): int
    {
        $file_name = 'sde/bsd/invFlags.yaml';

        /** @var FlagsFile $data */
        $data = Yaml::parseFile(Storage::path($file_name));

        $flag = ClassResolver::flag();

        foreach ($data as $values) {
            $flag::query()->updateOrInsert(['id' => $values['flagID']], [
                'name' => $values['flagName'],
                'text' => $values['flagText'] ?? '',
                'order_id' => $values['orderID'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->info(sprintf('Successfully seeded %d flags', count($data)));

        return self::SUCCESS;
    }
}
