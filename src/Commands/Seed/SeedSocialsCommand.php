<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Commands\Seed;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use NicolasKion\SDE\ClassResolver;
use Symfony\Component\Yaml\Yaml;

/**
 * @phpstan-type FactionsResponse array<int, array{
 *     faction_id: int,
 *     name: string,
 *     description: null|string,
 *     is_unique: boolean,
 *     corporation_id: int|null,
 *     militia_corporation_id: int|null,
 *     size_factor: float,
 *     solar_system_id: int,
 *     station_count: int,
 *     station_system_count: int,
 * }>
 * @phpstan-type CorporationsFile array<int, array{
 *     stationID: int|null,
 *     ceoID: int|null,
 *     creatorID: int|null,
 *     factionID: int|null,
 *     dateFounded: string|null,
 *     url: string|null,
 *     nameID: array{en: string|null},
 *     descriptionID: array{en: string|null},
 *     members: int|null,
 *     shares: int,
 *     taxRate: float,
 *     tickerName: string|null,
 * }>
 * @phpstan-type BloodlinesResponse array<int,array{
 *     bloodline_id: int,
 *     name: string,
 *     description: string|null,
 *     corporation_id: int,
 *     ship_type_id: int,
 *     race_id: int,
 *     intelligence: int,
 *     charisma: int,
 *     perception: int,
 *     memory: int,
 *     willpower: int,
 * }>
 */
class SeedSocialsCommand extends BaseSeedCommand
{
    protected $signature = 'sde:seed:socials';

    /**
     * @throws ConnectionException
     */
    public function handle(): int
    {
        $this->seedFactions();

        $this->seedCorps();

        $this->seedBloodlines();

        return self::SUCCESS;
    }

    /**
     * @throws ConnectionException
     */
    private function seedFactions(): void
    {
        $this->info('Fetching factions from ESI API');

        /** @var FactionsResponse $factionEsi */
        $factionEsi = Http::retry(5)->get('https://esi.evetech.net/latest/universe/factions/')->json();

        $faction = ClassResolver::faction();

        $upsertData = [];
        foreach ($factionEsi as $item) {
            $upsertData[] = [
                'id' => $item['faction_id'],
                'name' => $item['name'],
                'description' => $item['description'] ?? null,
                'corporation_id' => null, // seed later due to fk
                'is_unique' => $item['is_unique'],
                'militia_corporation_id' => null,
                'size_factor' => $item['size_factor'],
                'solarsystem_id' => $item['solar_system_id'],
                'station_count' => $item['station_count'],
                'station_system_count' => $item['station_system_count'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        $this->chunkedUpsert(
            $faction::query(),
            $upsertData,
            ['id'],
            ['name', 'description', 'corporation_id', 'is_unique', 'militia_corporation_id', 'size_factor', 'solarsystem_id', 'station_count', 'station_system_count', 'updated_at']
        );
    }

    private function seedCorps(): void
    {
        $file_name = 'sde/fsd/npcCorporations.yaml';

        $this->info(sprintf('Parsing corporations from %s', $file_name));

        /** @var CorporationsFile $data */
        $data = Yaml::parseFile(Storage::path($file_name));

        $corp = ClassResolver::corporation();

        /** @var int[] $char_ids */
        $char_ids = collect($data)->values()->pluck('ceoID')->unique()->whereNotNull()->all();

        $char = ClassResolver::character();

        $station = ClassResolver::station();

        $char::createFromIds($char_ids);

        // For corporations, we need to handle station lookups, so we'll keep individual processing
        // but collect data for bulk upsert
        $upsertData = [];
        foreach ($data as $key => $item) {
            $stationData = $station::query()->find($item['stationID'] ?? null);
            $upsertData[] = [
                'id' => $key,
                'ceo_id' => $item['ceoID'] ?? null,
                'creator_id' => null,
                'home_station_id' => $stationData->id ?? null,
                'faction_id' => $item['factionID'] ?? null,
                'date_founded' => $item['dateFounded'] ?? null,
                'url' => $item['url'] ?? null,
                'name' => $item['nameID']['en'],
                'description' => $item['descriptionID']['en'] ?? null,
                'member_count' => $item['members'] ?? null,
                'npc' => true,
                'shares' => $item['shares'],
                'tax_rate' => $item['taxRate'],
                'war_eligible' => false,
                'ticker' => $item['tickerName'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        $this->chunkedUpsert(
            $corp::query(),
            $upsertData,
            ['id'],
            ['ceo_id', 'creator_id', 'home_station_id', 'faction_id', 'date_founded', 'url', 'name', 'description', 'member_count', 'npc', 'shares', 'tax_rate', 'war_eligible', 'ticker', 'updated_at']
        );
    }

    /**
     * @throws ConnectionException
     */
    private function seedBloodlines(): void
    {
        $this->info('Fetching bloodlines from ESI API');

        /** @var BloodlinesResponse $bloodlinesEsi */
        $bloodlinesEsi = Http::retry(5)->get('https://esi.evetech.net/latest/universe/bloodlines/')->json();

        $bloodline = ClassResolver::bloodline();

        $upsertData = [];
        foreach ($bloodlinesEsi as $item) {
            $upsertData[] = [
                'id' => $item['bloodline_id'],
                'name' => $item['name'],
                'description' => $item['description'] ?? null,
                'corporation_id' => $item['corporation_id'],
                'ship_type_id' => $item['ship_type_id'],
                'race_id' => $item['race_id'],
                'intelligence' => $item['intelligence'],
                'charisma' => $item['charisma'],
                'perception' => $item['perception'],
                'memory' => $item['memory'],
                'willpower' => $item['willpower'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        $this->chunkedUpsert(
            $bloodline::query(),
            $upsertData,
            ['id'],
            ['name', 'description', 'corporation_id', 'ship_type_id', 'race_id', 'intelligence', 'charisma', 'perception', 'memory', 'willpower', 'updated_at']
        );
    }
}
