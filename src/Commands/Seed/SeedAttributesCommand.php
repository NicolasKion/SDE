<?php

namespace NicolasKion\SDE\Commands\Seed;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use NicolasKion\SDE\ClassResolver;
use Symfony\Component\Yaml\Yaml;

class SeedAttributesCommand extends Command
{
    protected $signature = 'sde:seed:attributes';

    /**
     * Run the database seeds.
     */
    public function handle(): int
    {
        $file_name = 'sde/fsd/dogmaAttributes.yaml';

        $data = Yaml::parseFile(Storage::path($file_name));

        $attribute_class = ClassResolver::attribute();

        foreach ($data as $id => $values) {
            $attribute_class::query()->updateOrCreate(
                ['id' => $id],
                [
                    'id' => $id,
                    'high_is_good' => $values['highIsGood'] ?? false,
                    'description' => $locals[$id]['description'] ?? '',
                    'default_value' => $values['defaultValue'] ?? 0,
                    'icon_id' => $values['iconID'] ?? null,
                    'published' => $values['published'] ?? false,
                    'display_name' => $values['displayNameID']['en'] ?? '',
                    'name' => $values['name'] ?? '',
                    'stackable' => $values['stackable'] ?? false,
                    'unit_id' => $values['unitID'] ?? null,
                ]
            );
        }

        $attribute_class::query()->where('display_name', 'rate of fire bonus')->update(['display_name' => 'Rate Of Fire Bonus']);

        $this->info(sprintf('Successfully seeded %d attributes', count($data)));

        return self::SUCCESS;
    }
}
