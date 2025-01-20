<?php

namespace NicolasKion\SDE\Commands\Seed;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use NicolasKion\SDE\ClassResolver;
use Symfony\Component\Yaml\Yaml;

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

    private function seedFactions()
    {
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

    private function seedCorps()
    {
        $file_name = 'sde/fsd/npcCorporations.yaml';

        $data = Yaml::parseFile(Storage::path($file_name));

        $corp = ClassResolver::corporation();

        $char_ids = collect($data)->values()->pluck('ceoID')->unique()->whereNotNull()->all();

        $char = ClassResolver::character();

        $station = ClassResolver::station();

        $char::createFromIds($char_ids);

        foreach ($data as $id => $values) {
            $stationData = $station::find($values['stationID'] ?? null);
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

    private function seedBloodlines()
    {
        $factionEsi = Http::retry(5)->get('https://esi.evetech.net/latest/universe/bloodlines/')->json();

        $bloodline = ClassResolver::bloodline();

        foreach ($factionEsi as $values) {
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
