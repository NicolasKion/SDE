<?php

namespace NicolasKion\SDE\Commands\Seed;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use NicolasKion\SDE\ClassResolver;
use Symfony\Component\Yaml\Yaml;

class SeedRacesCommand extends Command
{
    protected $signature = 'sde:seed:races';

    public function handle(): int
    {
        $file_name = 'sde/fsd/races.yaml';

        $data = Yaml::parseFile(Storage::path($file_name));

        $race = ClassResolver::race();

        foreach ($data as $id => $values) {
            $race::query()->updateOrInsert(['id' => $id], [
                'name' => $values['nameID']['en'],
                'description' => $values['descriptionID']['en'] ?? null,
                'icon_id' => $values['iconID'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->info(sprintf('Successfully seeded %d races', count($data)));

        return self::SUCCESS;
    }
}
