<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Commands\Seed;

use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use NicolasKion\SDE\ClassResolver;
use NicolasKion\SDE\Models\SolarsystemConnection;
use NicolasKion\SDE\Models\Stargate;
use Symfony\Component\Yaml\Yaml;
use Throwable;

/**
 * @phpstan-type NamesFile array<int, array{
 *     itemID: int|null,
 *     itemName: string|null,
 * }>
 * @phpstan-type RegionData array{
 *     regionID: int,
 * }
 * @phpstan-type ConstellationData array{
 *     regionID: int,
 *     constellationID: int,
 * }
 * @phpstan-type Station array{
 *       regionID: int,
 *       constellationID: int,
 *       solarSystemID: int,
 *       celestialID: int,
 *       typeID: int,
 *       itemName: string,
 * }
 * @phpstan-type Moon array{
 *      regionID: int,
 *      constellationID: int,
 *      solarSystemID: int,
 *      celestialID: int,
 *      typeID: int,
 *      itemName: string,
 *      npcStations: array<int,Station>|null
 * }
 * @phpstan-type Planet array{
 *       regionID: int,
 *       constellationID: int,
 *       solarSystemID: int,
 *       celestialID: int,
 *       typeID: int,
 *       itemName: string,
 *       moons: array<int,Moon>|null,
 *       npcStations: array<int,Station>|null,
 * }
 * @phpstan-type SolarsystemData array{
 *     regionID: int,
 *     constellationID: int,
 *     solarSystemID: int,
 *     security: string,
 *     center: array<int,int>,
 *     planets: array<int,Planet>|null,
 *     stargates: array<int,Stargate>|null,
 * }
 * @phpstan-type Stargate array{
 *     destination: int,
 *     position: array<int,int>,
 *     typeID: int,
 * }
 */
class SeedUniverseCommand extends BaseSeedCommand
{
    protected $signature = 'sde:seed:universe';

    /**
     * @throws Exception|Throwable
     */
    public function handle(): int
    {
        $this->ensureSDEExists();

        /** @var NamesFile $names */
        $names = Yaml::parseFile(Storage::path('sde/bsd/invUniqueNames.yaml'));

        /** @var Collection<int,array{itemName: string|null}> $names */
        $names = collect($names)->keyBy('itemID');

        /** @var string[] $directories */
        $directories = Storage::directories('sde/universe');

        $regionClass = ClassResolver::region();
        $constellationClass = ClassResolver::constellation();
        $solarsystemClass = ClassResolver::solarsystem();
        $celestialClass = ClassResolver::celestial();
        $stationClass = ClassResolver::station();

        foreach ($directories as $type) {
            $type_name = basename($type);

            /** @var string[] $regions */
            $regions = Storage::directories($type);

            foreach ($regions as $region) {
                /** @var RegionData $region_data */
                $region_data = Yaml::parseFile(sprintf('%s/region.yaml', Storage::path($region)));

                $regionClass::query()->updateOrInsert(['id' => $region_data['regionID']], [
                    'name' => $names[$region_data['regionID']]['itemName'] ?? '',
                    'type' => $type_name,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                /** @var string[] $constellations */
                $constellations = Storage::directories($region);

                foreach ($constellations as $constellation) {
                    /** @var ConstellationData $constellation_data */
                    $constellation_data = Yaml::parseFile(sprintf('%s/constellation.yaml', Storage::path($constellation)));

                    $constellationClass::query()->updateOrInsert(['id' => $constellation_data['constellationID']], [
                        'region_id' => $region_data['regionID'],
                        'name' => $names[$constellation_data['constellationID']]['itemName'] ?? '',
                        'type' => $type_name,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    /** @var string[] $solarsystems */
                    $solarsystems = Storage::directories($constellation);

                    foreach ($solarsystems as $solarsystem) {
                        /** @var SolarsystemData $solarsystem_data */
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

                        DB::transaction(function () use ($solarsystem_data, $celestialClass, $constellation_data, $region_data, $stationClass, $names) {

                            foreach ($solarsystem_data['planets'] ?? [] as $planet_id => $planet_data) {
                                $celestialClass::query()->updateOrInsert(['id' => $planet_id], [
                                    'solarsystem_id' => $solarsystem_data['solarSystemID'],
                                    'constellation_id' => $constellation_data['constellationID'],
                                    'region_id' => $region_data['regionID'],
                                    'name' => $names[$planet_id]['itemName'] ?? '',
                                    'type_id' => $planet_data['typeID'],
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
                                        'type_id' => $moon_data['typeID'],
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
                                            'type_id' => $station_data['typeID'],
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
                                        'type_id' => $station_data['typeID'],
                                        'parent_id' => $planet_id,
                                        'created_at' => now(),
                                        'updated_at' => now(),
                                    ]);
                                }
                            }
                        });

                        DB::transaction(function () use ($constellation_data, $solarsystem_data, $region_data) {
                            Schema::disableForeignKeyConstraints();
                            foreach ($solarsystem_data['stargates'] ?? [] as $stargate_id => $stargate_data) {
                                Stargate::query()->updateOrInsert(['id' => $stargate_id], [
                                    'solarsystem_id' => $solarsystem_data['solarSystemID'],
                                    'destination_id' => $stargate_data['destination'],
                                    'constellation_id' => $constellation_data['constellationID'],
                                    'region_id' => $region_data['regionID'],
                                    'position_x' => $stargate_data['position'][0],
                                    'position_y' => $stargate_data['position'][1],
                                    'position_z' => $stargate_data['position'][2],
                                    'type_id' => $stargate_data['typeID'],
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ]);
                            }
                            Schema::enableForeignKeyConstraints();
                        });
                    }
                }
            }
        }

        Stargate::query()->each(function (Stargate $stargate) {
            DB::transaction(function () use ($stargate) {
                Schema::disableForeignKeyConstraints();
                $destination = Stargate::query()->find($stargate->destination_id);
                SolarsystemConnection::query()->updateOrCreate([
                    'from_stargate_id' => $stargate->id,
                ], [
                    'from_solarsystem_id' => $stargate->solarsystem_id,
                    'from_region_id' => $stargate->region_id,
                    'from_constellation_id' => $stargate->constellation_id,
                    'to_stargate_id' => $stargate->destination_id,
                    'to_solarsystem_id' => $destination?->solarsystem_id,
                    'to_region_id' => $destination?->region_id,
                    'to_constellation_id' => $destination?->constellation_id,
                    'is_regional' => $stargate->region_id !== $destination?->region_id,
                ]);
                Schema::enableForeignKeyConstraints();
            });
        });

        $this->info('Successfully seeded universe');

        return self::SUCCESS;
    }
}
