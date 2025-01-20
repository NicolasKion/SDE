<?php

namespace NicolasKion\SDE\Commands\Seed;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use NicolasKion\SDE\ClassResolver;
use Symfony\Component\Yaml\Yaml;

class SeedUniverseCommand extends BaseSeedCommand
{
    protected $signature = 'sde:seed:universe';

    /**
     * @throws Exception
     */
    public function handle(): int
    {
        $this->ensureSDEExists();

        $names = Yaml::parseFile(Storage::path('sde/bsd/invUniqueNames.yaml'));

        $names = collect($names)->keyBy('itemID');

        $directories = Storage::directories('sde/universe');

        $regionClass = ClassResolver::region();
        $constellationClass = ClassResolver::constellation();
        $solarsystemClass = ClassResolver::solarsystem();
        $celestialClass = ClassResolver::celestial();
        $stationClass = ClassResolver::station();

        foreach ($directories as $type) {
            $type_name = basename($type);

            $regions = Storage::directories($type);

            foreach ($regions as $region) {
                $region_data = Yaml::parseFile(sprintf('%s/region.yaml', Storage::path($region)));

                $regionClass::query()->updateOrInsert(['id' => $region_data['regionID']], [
                    'name' => $names[$region_data['regionID']]['itemName'] ?? '',
                    'type' => $type_name,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $constellations = Storage::directories($region);

                foreach ($constellations as $constellation) {
                    $constellation_data = Yaml::parseFile(sprintf('%s/constellation.yaml', Storage::path($constellation)));

                    $constellationClass::query()->updateOrInsert(['id' => $constellation_data['constellationID']], [
                        'region_id' => $region_data['regionID'],
                        'name' => $names[$constellation_data['constellationID']]['itemName'] ?? '',
                        'type' => $type_name,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $solarsystems = Storage::directories($constellation);

                    foreach ($solarsystems as $solarsystem) {
                        $solarsystem_data = Yaml::parseFile(sprintf('%s/solarsystem.yaml', Storage::path($solarsystem)));

                        $solarsystemClass::query()->updateOrInsert(['id' => $solarsystem_data['solarSystemID']], [
                            'constellation_id' => $constellation_data['constellationID'],
                            'region_id' => $region_data['regionID'],
                            'name' => $names[$solarsystem_data['solarSystemID']]['itemName'] ?? '',
                            'type' => $type_name,
                            'security' => $solarsystem_data['security'],
                            'pos_x' => $solarsystem_data['center'][0],
                            'pos_y' => $solarsystem_data['center'][1],
                            'pos_z' => $solarsystem_data['center'][2],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        DB::transaction(function () use ($solarsystem_data, $celestialClass, $constellation_data, $region_data, $stationClass) {

                            foreach ($solarsystem_data['planets'] ?? [] as $planet_id => $planet_data) {
                                $celestialClass::query()->updateOrInsert(['id' => $planet_id], [
                                    'solarsystem_id' => $solarsystem_data['solarSystemID'],
                                    'constellation_id' => $constellation_data['constellationID'],
                                    'region_id' => $region_data['regionID'],
                                    'name' => $names[$planet_id]['itemName'] ?? '',
                                    'type_id' => $planet_data['typeID'] ?? null,
                                    'group_id' => 7,
                                    'parent_id' => null,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ]);

                                foreach ($planet_data['moons'] ?? [] as $moon_id => $moon_data) {
                                    $celestialClass::query()->updateOrInsert(['id' => $moon_id], [
                                        'solarsystem_id' => $solarsystem_data['solarSystemID'],
                                        'constellation_id' => $constellation_data['constellationID'],
                                        'region_id' => $region_data['regionID'],
                                        'name' => $names[$moon_id]['itemName'] ?? '',
                                        'type_id' => $moon_data['typeID'] ?? null,
                                        'parent_id' => $planet_id,
                                        'group_id' => 8,
                                        'created_at' => now(),
                                        'updated_at' => now(),
                                    ]);

                                    foreach ($moon_data['npcStations'] ?? [] as $station_id => $station_data) {
                                        $stationClass::query()->updateOrInsert(['id' => $station_id], [
                                            'solarsystem_id' => $solarsystem_data['solarSystemID'],
                                            'constellation_id' => $constellation_data['constellationID'],
                                            'region_id' => $region_data['regionID'],
                                            'name' => $names[$station_id]['itemName'] ?? '',
                                            'type_id' => $station_data['typeID'] ?? null,
                                            'parent_id' => $moon_id,
                                            'created_at' => now(),
                                            'updated_at' => now(),
                                        ]);
                                    }
                                }

                                foreach ($planet_data['npcStations'] ?? [] as $station_id => $station_data) {
                                    $stationClass::query()->updateOrInsert(['id' => $station_id], [
                                        'solarsystem_id' => $solarsystem_data['solarSystemID'],
                                        'constellation_id' => $constellation_data['constellationID'],
                                        'region_id' => $region_data['regionID'],
                                        'name' => $names[$station_id]['itemName'] ?? '',
                                        'type_id' => $station_data['typeID'] ?? null,
                                        'parent_id' => $planet_id,
                                        'created_at' => now(),
                                        'updated_at' => now(),
                                    ]);
                                }
                            }
                        });
                    }
                }
            }
        }

        $this->info('Successfully seeded universe');

        return self::SUCCESS;
    }
}
