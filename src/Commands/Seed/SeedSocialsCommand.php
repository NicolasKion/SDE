<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Commands\Seed;

use Illuminate\Console\Command;
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
 *
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
 * }>
 *
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
class SeedSocialsCommand extends Command
{
    protected $signature = 'sde:seed:socials';

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
        /** @var FactionsResponse $factionEsi */
        $factionEsi = Http::retry(5)->get('https://esi.evetech.net/latest/universe/factions/')->json();

        $faction = ClassResolver::faction();

        foreach ($factionEsi as $values) {
            $faction::query()->updateOrInsert(['id' => $values['faction_id']], [
                'name' => $values['name'],
                'description' => $values['description'] ?? null,
                'corporation_id' => null, // seed later due to fk
                'is_unique' => $values['is_unique'],
                'militia_corporation_id' => null,
                'size_factor' => $values['size_factor'],
                'solarsystem_id' => $values['solar_system_id'],
                'station_count' => $values['station_count'],
                'station_system_count' => $values['station_system_count']
            ]);
        }
    }

    private function seedCorps(): void
    {
        $file_name = 'sde/fsd/npcCorporations.yaml';

        /** @var CorporationsFile $data */
        $data = Yaml::parseFile(Storage::path($file_name));

        $corp = ClassResolver::corporation();

        /** @var int[] $char_ids */
        $char_ids = collect($data)->values()->pluck('ceoID')->unique()->whereNotNull()->all();

        $char = ClassResolver::character();

        $station = ClassResolver::station();

        $char::createFromIds($char_ids);

        foreach ($data as $id => $values) {
            $stationData = $station::query()->find($values['stationID'] ?? null);
            $corp::query()->updateOrInsert(['id' => $id], [
                'ceo_id' => $values['ceoID'] ?? null,
                'creator_id' => null,
                'home_station_id' => $stationData->id ?? null,
                'faction_id' => $values['factionID'] ?? null,
                'date_founded' => $values['dateFounded'] ?? null,
                'url' => $values['url'] ?? null,
                'name' => $values['nameID']['en'],
                'description' => $values['descriptionID']['en'] ?? null,
                'member_count' => $values['members'] ?? null,
                'npc' => true,
                'shares' => $values['shares'],
                'tax_rate' => $values['taxRate'],
                'war_eligible' => false,
            ]);
        }
    }

    /**
     * @throws ConnectionException
     */
    private function seedBloodlines(): void
    {
        /** @var BloodlinesResponse $bloodlinesEsi */
        $bloodlinesEsi = Http::retry(5)->get('https://esi.evetech.net/latest/universe/bloodlines/')->json();

        $bloodline = ClassResolver::bloodline();

        foreach ($bloodlinesEsi as $values) {
            $bloodline::query()->updateOrInsert(['id' => $values['bloodline_id']], [
                'name' => $values['name'],
                'description' => $values['description'] ?? null,
                'corporation_id' => $values['corporation_id'],
                'ship_type_id' => $values['ship_type_id'],
                'race_id' => $values['race_id'],
                'intelligence' => $values['intelligence'],
                'charisma' => $values['charisma'],
                'perception' => $values['perception'],
                'memory' => $values['memory'],
                'willpower' => $values['willpower'],
            ]);
        }
    }
}
