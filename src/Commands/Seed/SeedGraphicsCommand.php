<?php

namespace NicolasKion\SDE\Commands\Seed;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use NicolasKion\SDE\ClassResolver;
use Symfony\Component\Yaml\Yaml;

class SeedGraphicsCommand extends Command
{
    protected $signature = 'sde:seed:graphics';

    public function handle(): int
    {
        $file_name = 'sde/fsd/graphicIDs.yaml';

        $data = Yaml::parseFile(Storage::path($file_name));

        $graphic = ClassResolver::graphic();

        foreach ($data as $id => $values) {
            $graphic::query()->updateOrInsert(['id' => $id], [
                'file' => $values['iconInfo']['folder'] ?? null,
                'sof_faction_name' => $values['sofFactionName'] ?? null,
                'sof_hull_name' => $values['sofHullName'] ?? null,
                'sof_race_name' => $values['sofRaceName'] ?? null,
            ]);
        }

        $this->info(sprintf('Successfully seeded %d graphics', count($data)));

        return self::SUCCESS;
    }
}
