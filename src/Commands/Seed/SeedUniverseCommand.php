<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Commands\Seed;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use NicolasKion\SDE\ClassResolver;
use NicolasKion\SDE\Models\SolarsystemConnection;
use NicolasKion\SDE\Models\Stargate;
use NicolasKion\SDE\Support\JSONL;
use NicolasKion\SDE\Support\UniverseHelpers;
use Throwable;

use function array_flip;
use function collect;

/**
 * @phpstan-type RegionData array{
 *     _key: int,
 *     name: array{en: string|null},
 *     constellationIDs: array<int>,
 * }
 * @phpstan-type ConstellationData array{
 *     _key: int,
 *     regionID: int,
 *     name: array{en: string|null},
 *     solarSystemIDs: array<int>,
 * }
 * @phpstan-type SolarsystemData array{
 *     _key: int,
 *     regionID: int,
 *     constellationID: int,
 *     name: array{en: string|null},
 *     securityStatus: float,
 *     position: array{x: float, y: float, z: float},
 *     starID: int,
 *     planetIDs: array<int>,
 * }
 * @phpstan-type PlanetData array{
 *     _key: int,
 *     solarSystemID: int,
 *     celestialIndex: int,
 *     orbitID: int,
 *     moonIDs: array<int>,
 *     asteroidBeltIDs: array<int>,
 *     typeID: int,
 * }
 * @phpstan-type MoonData array{
 *     _key: int,
 *     solarSystemID: int,
 *     celestialIndex: int,
 *     orbitID: int,
 *     orbitIndex: int,
 *     typeID: int,
 * }
 * @phpstan-type AsteroidBeltData array{
 *     _key: int,
 *     solarSystemID: int,
 *     celestialIndex: int,
 *     orbitID: int,
 *     orbitIndex: int,
 * }
 * @phpstan-type StarData array{
 *     _key: int,
 *     solarSystemID: int,
 *     typeID: int,
 * }
 * @phpstan-type StationData array{
 *     _key: int,
 *     solarSystemID: int,
 *     orbitID: int,
 *     orbitIndex: int,
 *     celestialIndex: int,
 *     ownerID: int,
 *     operationID: int|null,
 *     useOperationName: bool,
 *     typeID: int,
 * }
 * @phpstan-type StargateData array{
 *     _key: int,
 *     solarSystemID: int,
 *     destination: array{solarSystemID: int, stargateID: int},
 *     position: array{x: float, y: float, z: float},
 *     typeID: int,
 * }
 * @phpstan-type StationOperationData array{
 *     _key: int,
 *     operationName: array{en: string|null},
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

        $this->info('Loading universe data from JSONL files...');

        // Load all JSONL files
        /** @var RegionData[] $regionsData */
        $regionsData = JSONL::parse(Storage::path('sde/mapRegions.jsonl'));

        /** @var ConstellationData[] $constellationsData */
        $constellationsData = JSONL::parse(Storage::path('sde/mapConstellations.jsonl'));

        /** @var SolarsystemData[] $solarsystemsData */
        $solarsystemsData = JSONL::parse(Storage::path('sde/mapSolarSystems.jsonl'));

        /** @var PlanetData[] $planetsData */
        $planetsData = JSONL::parse(Storage::path('sde/mapPlanets.jsonl'));

        /** @var MoonData[] $moonsData */
        $moonsData = JSONL::parse(Storage::path('sde/mapMoons.jsonl'));

        /** @var AsteroidBeltData[] $asteroidBeltsData */
        $asteroidBeltsData = JSONL::parse(Storage::path('sde/mapAsteroidBelts.jsonl'));

        /** @var StarData[] $starsData */
        $starsData = JSONL::parse(Storage::path('sde/mapStars.jsonl'));

        /** @var StationData[] $stationsData */
        $stationsData = JSONL::parse(Storage::path('sde/npcStations.jsonl'));

        /** @var StargateData[] $stargatesData */
        $stargatesData = JSONL::parse(Storage::path('sde/mapStargates.jsonl'));

        /** @var StationOperationData[] $stationOperationsData */
        $stationOperationsData = JSONL::parse(Storage::path('sde/stationOperations.jsonl'));

        // Load corporation names
        $corporationsData = JSONL::parse(Storage::path('sde/npcCorporations.jsonl'));
        $corporationNames = collect($corporationsData)->keyBy('_key')->map(fn ($corp) => $corp['name']['en'] ?? '');

        // Load operation names
        $operationNames = collect($stationOperationsData)->keyBy('_key')->map(fn ($op) => $op['operationName']['en'] ?? '');

        // Build lookup maps
        $regionsMap = collect($regionsData)->keyBy('_key');
        $constellationsMap = collect($constellationsData)->keyBy('_key');
        $solarsystemsMap = collect($solarsystemsData)->keyBy('_key');
        $planetsMap = collect($planetsData)->keyBy('_key');
        $moonsMap = collect($moonsData)->keyBy('_key');
        $starsMap = collect($starsData)->keyBy('_key');

        // Load Jove observatories
        $jove_observatories = require __DIR__.'/../../Data/jove_observatories.php';
        $jove_observatories_flat = [];
        foreach ($jove_observatories as $regionName => $systems) {
            $jove_observatories_flat = array_merge($jove_observatories_flat, array_flip($systems));
        }

        $regionClass = ClassResolver::region();
        $constellationClass = ClassResolver::constellation();
        $solarsystemClass = ClassResolver::solarsystem();
        $celestialClass = ClassResolver::celestial();
        $stationClass = ClassResolver::station();

        // Collect all data first, then perform bulk operations
        $this->info('Collecting universe data...');
        $regionsToInsert = [];
        $constellationsToInsert = [];
        $solarsystemsToInsert = [];
        $celestialsToInsert = [];
        $stationsToInsert = [];
        $stargatesToInsert = [];

        // Process regions
        foreach ($regionsData as $region) {
            $regionId = $region['_key'];
            $regionName = $region['name']['en'] ?? '';
            $areaType = UniverseHelpers::determineAreaType($regionId);

            $regionsToInsert[] = [
                'id' => $regionId,
                'name' => $regionName,
                'type' => $areaType,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Process constellations
        foreach ($constellationsData as $constellation) {
            $constellationId = $constellation['_key'];
            $constellationName = $constellation['name']['en'] ?? '';
            $areaType = UniverseHelpers::determineAreaType($constellationId);

            $constellationsToInsert[] = [
                'id' => $constellationId,
                'region_id' => $constellation['regionID'],
                'name' => $constellationName,
                'type' => $areaType,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Process solar systems
        foreach ($solarsystemsData as $solarsystem) {
            $solarsystemId = $solarsystem['_key'];
            $solarsystemName = $solarsystem['name']['en'] ?? '';
            $areaType = UniverseHelpers::determineAreaType($solarsystemId);
            $has_jove_observatory = isset($jove_observatories_flat[$solarsystemName]);

            $solarsystemsToInsert[] = [
                'id' => $solarsystemId,
                'constellation_id' => $solarsystem['constellationID'],
                'region_id' => $solarsystem['regionID'],
                'name' => $solarsystemName,
                'type' => $areaType,
                'security' => (string) $solarsystem['securityStatus'],
                'pos_x' => $solarsystem['position']['x'],
                'pos_y' => $solarsystem['position']['y'],
                'pos_z' => $solarsystem['position']['z'],
                'has_jove_observatory' => $has_jove_observatory,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Process stars
        foreach ($starsData as $star) {
            $starId = $star['_key'];
            $solarsystem = $solarsystemsMap->get($star['solarSystemID']);
            if (! $solarsystem) {
                continue;
            }

            $solarsystemName = $solarsystem['name']['en'] ?? '';
            $starName = UniverseHelpers::generateStarName($solarsystemName);

            $celestialsToInsert[] = [
                'id' => $starId,
                'solarsystem_id' => $star['solarSystemID'],
                'constellation_id' => $solarsystem['constellationID'],
                'region_id' => $solarsystem['regionID'],
                'name' => $starName,
                'type_id' => $star['typeID'],
                'group_id' => 6, // Star group
                'parent_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Process planets
        foreach ($planetsData as $planet) {
            $planetId = $planet['_key'];
            $solarsystem = $solarsystemsMap->get($planet['solarSystemID']);
            if (! $solarsystem) {
                continue;
            }

            // Get orbit name (star name for planets)
            $solarsystemName = $solarsystem['name']['en'] ?? '';
            $planetName = UniverseHelpers::generatePlanetName($solarsystemName, $planet['celestialIndex']);

            $celestialsToInsert[] = [
                'id' => $planetId,
                'solarsystem_id' => $planet['solarSystemID'],
                'constellation_id' => $solarsystem['constellationID'],
                'region_id' => $solarsystem['regionID'],
                'name' => $planetName,
                'type_id' => $planet['typeID'],
                'group_id' => 7, // Planet group
                'parent_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Process moons
        foreach ($moonsData as $moon) {
            $moonId = $moon['_key'];
            $solarsystem = $solarsystemsMap->get($moon['solarSystemID']);
            if (! $solarsystem) {
                continue;
            }

            // Get orbit name (planet name)
            $planet = $planetsMap->get($moon['orbitID']);
            if (! $planet) {
                continue;
            }

            $solarsystemName = $solarsystem['name']['en'] ?? '';
            $planetName = UniverseHelpers::generatePlanetName($solarsystemName, $planet['celestialIndex']);
            $moonName = UniverseHelpers::generateMoonName($planetName, $moon['orbitIndex']);

            $celestialsToInsert[] = [
                'id' => $moonId,
                'solarsystem_id' => $moon['solarSystemID'],
                'constellation_id' => $solarsystem['constellationID'],
                'region_id' => $solarsystem['regionID'],
                'name' => $moonName,
                'type_id' => $moon['typeID'],
                'group_id' => 8, // Moon group
                'parent_id' => $moon['orbitID'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Process asteroid belts
        foreach ($asteroidBeltsData as $asteroidBelt) {
            $beltId = $asteroidBelt['_key'];
            $solarsystem = $solarsystemsMap->get($asteroidBelt['solarSystemID']);
            if (! $solarsystem) {
                continue;
            }

            // Get orbit name (planet name)
            $planet = $planetsMap->get($asteroidBelt['orbitID']);
            if (! $planet) {
                continue;
            }

            $solarsystemName = $solarsystem['name']['en'] ?? '';
            $planetName = UniverseHelpers::generatePlanetName($solarsystemName, $planet['celestialIndex']);
            $beltName = UniverseHelpers::generateAsteroidBeltName($planetName, $asteroidBelt['orbitIndex']);

            $celestialsToInsert[] = [
                'id' => $beltId,
                'solarsystem_id' => $asteroidBelt['solarSystemID'],
                'constellation_id' => $solarsystem['constellationID'],
                'region_id' => $solarsystem['regionID'],
                'name' => $beltName,
                'type_id' => null, // Asteroid belts don't have typeID in the data
                'group_id' => 9, // Asteroid Belt group
                'parent_id' => $asteroidBelt['orbitID'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Process stations
        foreach ($stationsData as $station) {
            $stationId = $station['_key'];
            $solarsystem = $solarsystemsMap->get($station['solarSystemID']);
            if (! $solarsystem) {
                continue;
            }

            // Get orbit name (planet or moon name)
            $orbitName = '';
            $parentId = null;

            if ($planetsMap->has($station['orbitID'])) {
                $planet = $planetsMap->get($station['orbitID']);
                $solarsystemName = $solarsystem['name']['en'] ?? '';
                $orbitName = UniverseHelpers::generatePlanetName($solarsystemName, $planet['celestialIndex']);
                $parentId = $station['orbitID'];
            } elseif ($moonsMap->has($station['orbitID'])) {
                $moon = $moonsMap->get($station['orbitID']);
                $planet = $planetsMap->get($moon['orbitID']);
                $solarsystemName = $solarsystem['name']['en'] ?? '';
                $planetName = UniverseHelpers::generatePlanetName($solarsystemName, $planet['celestialIndex']);
                $orbitName = UniverseHelpers::generateMoonName($planetName, $moon['orbitIndex']);
                $parentId = $station['orbitID'];
            }

            // Get corporation name
            $corporationName = $corporationNames->get($station['ownerID']) ?? 'Unknown Corporation';

            // Get operation name if needed
            $operationName = null;
            if ($station['useOperationName'] && $station['operationID'] !== null) {
                $operationName = $operationNames->get($station['operationID']);
            }

            $stationName = UniverseHelpers::generateStationName(
                $orbitName,
                $corporationName,
                $operationName,
                $station['useOperationName']
            );

            $stationsToInsert[] = [
                'id' => $stationId,
                'solarsystem_id' => $station['solarSystemID'],
                'constellation_id' => $solarsystem['constellationID'],
                'region_id' => $solarsystem['regionID'],
                'name' => $stationName,
                'type_id' => $station['typeID'],
                'parent_id' => $parentId,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Process stargates
        foreach ($stargatesData as $stargate) {
            $stargateId = $stargate['_key'];
            $solarsystem = $solarsystemsMap->get($stargate['solarSystemID']);
            if (! $solarsystem) {
                continue;
            }

            $solarsystemName = $solarsystem['name']['en'] ?? '';
            $stargateName = UniverseHelpers::generateStargateName($solarsystemName);

            $stargatesToInsert[] = [
                'id' => $stargateId,
                'solarsystem_id' => $stargate['solarSystemID'],
                'destination_id' => $stargate['destination']['stargateID'],
                'constellation_id' => $solarsystem['constellationID'],
                'region_id' => $solarsystem['regionID'],
                'position_x' => $stargate['position']['x'],
                'position_y' => $stargate['position']['y'],
                'position_z' => $stargate['position']['z'],
                'type_id' => $stargate['typeID'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Insert all data
        $this->chunkedUpsert(
            $regionClass::query(),
            $regionsToInsert,
            ['id'],
            ['name', 'type', 'updated_at'],
            'Upserting regions'
        );

        $this->chunkedUpsert(
            $constellationClass::query(),
            $constellationsToInsert,
            ['id'],
            ['region_id', 'name', 'type', 'updated_at'],
            'Upserting constellations'
        );

        $this->chunkedUpsert(
            $solarsystemClass::query(),
            $solarsystemsToInsert,
            ['id'],
            ['constellation_id', 'region_id', 'name', 'type', 'security', 'pos_x', 'pos_y', 'pos_z', 'updated_at', 'has_jove_observatory'],
            'Upserting solarsystems'
        );

        $this->chunkedUpsert(
            $celestialClass::query(),
            $celestialsToInsert,
            ['id'],
            ['solarsystem_id', 'constellation_id', 'region_id', 'name', 'type_id', 'group_id', 'parent_id', 'updated_at'],
            'Upserting celestials'
        );

        $this->chunkedUpsert(
            $stationClass::query(),
            $stationsToInsert,
            ['id'],
            ['solarsystem_id', 'constellation_id', 'region_id', 'name', 'type_id', 'parent_id', 'updated_at'],
            'Upserting stations'
        );

        DB::transaction(function () use ($stargatesToInsert) {
            Schema::disableForeignKeyConstraints();
            $this->chunkedUpsert(
                Stargate::query(),
                $stargatesToInsert,
                ['id'],
                ['solarsystem_id', 'destination_id', 'constellation_id', 'region_id', 'position_x', 'position_y', 'position_z', 'type_id', 'updated_at'],
                'Upserting stargates'
            );
            Schema::enableForeignKeyConstraints();
        });

        $this->info('Generating stargate connections...');

        // Get all stargates with their destination info in one query
        $stargates = Stargate::query()
            ->select(['id', 'destination_id', 'solarsystem_id', 'region_id', 'constellation_id'])
            ->get()
            ->keyBy('id');

        $connectionsData = [];
        foreach ($stargates as $stargate) {
            $destination = $stargates->get($stargate->destination_id);
            $connectionsData[] = [
                'from_stargate_id' => $stargate->id,
                'from_solarsystem_id' => $stargate->solarsystem_id,
                'from_region_id' => $stargate->region_id,
                'from_constellation_id' => $stargate->constellation_id,
                'to_stargate_id' => $stargate->destination_id,
                'to_solarsystem_id' => $destination?->solarsystem_id,
                'to_region_id' => $destination?->region_id,
                'to_constellation_id' => $destination?->constellation_id,
                'is_regional' => $stargate->region_id !== $destination?->region_id,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::transaction(function () use ($connectionsData) {
            Schema::disableForeignKeyConstraints();

            $this->chunkedUpsert(
                SolarsystemConnection::query(),
                $connectionsData,
                ['from_stargate_id'],
                ['from_solarsystem_id', 'from_region_id', 'from_constellation_id', 'to_stargate_id', 'to_solarsystem_id', 'to_region_id', 'to_constellation_id', 'is_regional', 'updated_at'],
                'Upserting stargate connections'
            );

            Schema::enableForeignKeyConstraints();
        });

        $this->info(sprintf(
            'Successfully seeded universe: %d regions, %d constellations, %d solarsystems, %d celestials, %d stations, %d stargates, %d connections',
            count($regionsToInsert),
            count($constellationsToInsert),
            count($solarsystemsToInsert),
            count($celestialsToInsert),
            count($stationsToInsert),
            count($stargatesToInsert),
            count($connectionsData)
        ));

        return self::SUCCESS;
    }
}
