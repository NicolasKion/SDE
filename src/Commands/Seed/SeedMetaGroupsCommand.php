<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Commands\Seed;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use NicolasKion\SDE\ClassResolver;
use NicolasKion\SDE\Support\JSONL;
use Throwable;

/**
 * @phpstan-type MetaGroupsFile array{
 *     _key: int,
 *     name: array{en:string|null},
 *     iconID: int|null,
 *     iconSuffix: string|null,
 *     description: array{en:string|null},
 * }[]
 */
class SeedMetaGroupsCommand extends BaseSeedCommand
{
    protected $signature = 'sde:seed:meta-groups';

    /**
     * @throws Throwable
     */
    public function handle(): int
    {
        /** @var MetaGroupsFile $data */
        $data = JSONL::parse(Storage::path('sde/metaGroups.jsonl'));

        $metaGroup = ClassResolver::metaGroup();

        $upsertData = [];
        foreach ($data as $key => $item) {
            $upsertData[] = [
                'id' => $key,
                'name' => $item['name']['en'] ?? null,
                'icon_id' => $item['iconID'] ?? null,
                'icon_suffix' => $item['iconSuffix'] ?? null,
                'description' => $item['description']['en'] ?? null,
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
