<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Commands\Seed;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use NicolasKion\SDE\ClassResolver;
use Symfony\Component\Yaml\Yaml;
use Throwable;

/**
 * @phpstan-type MetaGroupsFile array<int, array{
 *     nameID: array{en:string|null},
 *     iconID: int|null,
 *     iconSuffix: string|null,
 *     descriptionID: array{en:string|null},
 * }>
 */
class SeedMetaGroupsCommand extends BaseSeedCommand
{
    protected $signature = 'sde:seed:meta-groups';

    /**
     * @throws Throwable
     */
    public function handle(): int
    {
        $file_name = 'sde/fsd/metaGroups.yaml';

        $this->info(sprintf('Parsing meta groups from %s', $file_name));

        /** @var MetaGroupsFile $data */
        $data = Yaml::parseFile(Storage::path($file_name));

        $metaGroup = ClassResolver::metaGroup();

        $upsertData = [];
        foreach ($data as $key => $item) {
            $upsertData[] = [
                'id' => $key,
                'name' => $item['nameID']['en'],
                'icon_id' => $item['iconID'] ?? null,
                'icon_suffix' => $item['iconSuffix'] ?? null,
                'description' => $item['descriptionID']['en'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::transaction(function () use ($upsertData, $metaGroup) {
            $this->chunkedUpsert(
                $metaGroup::query(),
                $upsertData,
                ['id'],
                ['name', 'icon_id', 'icon_suffix', 'description', 'updated_at']
            );
        });

        $this->info(sprintf('Successfully seeded %d meta groups', count($data)));

        return self::SUCCESS;
    }
}
