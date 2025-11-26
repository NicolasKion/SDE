<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Commands\Seed;

use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use NicolasKion\SDE\ClassResolver;
use NicolasKion\SDE\Data\Dto\AsteroidBeltDto;
use NicolasKion\SDE\Data\Dto\ConstellationDto;
use NicolasKion\SDE\Data\Dto\CorporationDto;
use NicolasKion\SDE\Data\Dto\MoonDto;
use NicolasKion\SDE\Data\Dto\PlanetDto;
use NicolasKion\SDE\Data\Dto\RegionDto;
use NicolasKion\SDE\Data\Dto\SolarsystemDto;
use NicolasKion\SDE\Data\Dto\StarDto;
use NicolasKion\SDE\Data\Dto\StargateDto;
use NicolasKion\SDE\Data\Dto\StationDto;
use NicolasKion\SDE\Data\Dto\StationOperationDto;
use NicolasKion\SDE\Models\SolarsystemConnection;
use NicolasKion\SDE\Models\Stargate;
use NicolasKion\SDE\Support\JSONL;
use NicolasKion\SDE\Support\UniverseHelpers;
use Throwable;

use function array_flip;
use function collect;
use function Laravel\Prompts\spin;

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
        $this->startMemoryTracking();

        // Seed each entity type in hierarchical order
        // Each method handles its own scope and memory cleanup
        $regionCount = $this->seedRegions();
        $this->logMemoryUsage('Regions Complete');

        $constellationCount = $this->seedConstellations();
        $this->logMemoryUsage('Constellations Complete');

        $solarsystemsLookup = $this->seedSolarsystems();
        $this->logMemoryUsage('Solarsystems Complete');

        $starCount = $this->seedStars($solarsystemsLookup);
        $this->logMemoryUsage('Stars Complete');

        $planetsLookup = $this->seedPlanets($solarsystemsLookup);
        $this->logMemoryUsage('Planets Complete');

        $moonsLookup = $this->seedMoons($solarsystemsLookup, $planetsLookup);
        $this->logMemoryUsage('Moons Complete');

        $beltCount = $this->seedAsteroidBelts($solarsystemsLookup, $planetsLookup);
        $this->logMemoryUsage('Asteroid Belts Complete');

        $stationCount = $this->seedStations($solarsystemsLookup, $planetsLookup, $moonsLookup);
        $this->logMemoryUsage('Stations Complete');

        [$stargateCount, $connectionCount] = $this->seedStargatesAndConnections($solarsystemsLookup);
        $this->logMemoryUsage('Stargates Complete');

        $totalRecords = $regionCount + $constellationCount + count($solarsystemsLookup) +
                        $starCount + count($planetsLookup) + count($moonsLookup) +
                        $beltCount + $stationCount + $stargateCount + $connectionCount;

        $this->displayMemoryStats($totalRecords);

        return self::SUCCESS;
    }

    /**
     * Seed regions (top level of universe hierarchy)
     *
     * @return int Number of regions seeded
     */
    protected function seedRegions(): int
    {
        $regionClass = ClassResolver::region();

        return $this->streamUpsert(
            $regionClass::query(),
            JSONL::lazy(Storage::path('sde/mapRegions.jsonl'), RegionDto::class),
            function (RegionDto $dto) {
                $areaType = UniverseHelpers::determineAreaType($dto->id);

                return [
                    'id' => $dto->id,
                    'name' => $dto->name,
                    'type' => $areaType,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            },
            ['id'],
            ['name', 'type', 'updated_at'],
            'Seeding Regions'
        );
    }

    /**
     * Seed constellations (belong to regions)
     *
     * @return int Number of constellations seeded
     */
    protected function seedConstellations(): int
    {
        $constellationClass = ClassResolver::constellation();

        return $this->streamUpsert(
            $constellationClass::query(),
            JSONL::lazy(Storage::path('sde/mapConstellations.jsonl'), ConstellationDto::class),
            function (ConstellationDto $dto) {
                $areaType = UniverseHelpers::determineAreaType($dto->id);

                return [
                    'id' => $dto->id,
                    'region_id' => $dto->regionId,
                    'name' => $dto->name,
                    'type' => $areaType,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            },
            ['id'],
            ['region_id', 'name', 'type', 'updated_at'],
            'Seeding Constellations'
        );
    }

    /**
     * Seed solar systems and build lookup map for child entities
     *
     * @return array<int, array{constellationID: int, regionID: int, name: string}>
     */
    protected function seedSolarsystems(): array
    {
        $solarsystemClass = ClassResolver::solarsystem();

        // Load Jove observatories data for special system flagging
        /** @var array<int,int[]> $jove_observatories */
        $jove_observatories = require __DIR__.'/../../Data/jove_observatories.php';
        $jove_observatories_flat = [];
        foreach ($jove_observatories as $systems) {
            $jove_observatories_flat = array_merge($jove_observatories_flat, array_flip($systems));
        }

        // Build lightweight lookup for child entities
        $solarsystemsLookup = [];

        $this->streamUpsert(
            $solarsystemClass::query(),
            JSONL::lazy(Storage::path('sde/mapSolarSystems.jsonl'), SolarsystemDto::class),
            function (SolarsystemDto $solarsystem) use (&$solarsystemsLookup, $jove_observatories_flat) {
                return $this->transformSolarsystem($solarsystem, $solarsystemsLookup, $jove_observatories_flat);
            },
            ['id'],
            ['constellation_id', 'region_id', 'name', 'type', 'security', 'pos_x', 'pos_y', 'pos_z', 'updated_at', 'has_jove_observatory'],
            'Seeding Solar Systems'
        );

        return $solarsystemsLookup;
    }

    /**
     * Transform solar system data for database insertion
     *
     * @param  array<int, array{constellationID: int, regionID: int, name: string}>  $solarsystemsLookup
     * @param  int[]  $jove_observatories_flat
     * @return array{id: int, constellation_id: int, region_id: int, name: string, type: string, security: string, pos_x: float, pos_y: float, pos_z: float, has_jove_observatory: bool, created_at: Carbon, updated_at: Carbon}
     */
    private function transformSolarsystem(SolarsystemDto $dto, array &$solarsystemsLookup, array $jove_observatories_flat): array
    {
        // Store in lookup for children
        $solarsystemsLookup[$dto->id] = [
            'constellationID' => $dto->constellationId,
            'regionID' => $dto->regionId,
            'name' => $dto->name,
        ];

        $areaType = UniverseHelpers::determineAreaType($dto->id);
        $has_jove_observatory = isset($jove_observatories_flat[$dto->name]);

        return [
            'id' => $dto->id,
            'constellation_id' => $dto->constellationId,
            'region_id' => $dto->regionId,
            'name' => $dto->name,
            'type' => $areaType,
            'security' => (string) $dto->securityStatus,
            'pos_x' => $dto->position->x,
            'pos_y' => $dto->position->y,
            'pos_z' => $dto->position->z,
            'has_jove_observatory' => $has_jove_observatory,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Seed stars (central celestial body in each system)
     *
     * @param  array<int, array{constellationID: int, regionID: int, name: string}>  $solarsystemsLookup
     * @return int Number of stars seeded
     */
    protected function seedStars(array $solarsystemsLookup): int
    {
        $celestialClass = ClassResolver::celestial();

        return $this->streamUpsert(
            $celestialClass::query(),
            JSONL::lazy(Storage::path('sde/mapStars.jsonl'), StarDto::class),
            fn (StarDto $star) => $this->transformStar($star, $solarsystemsLookup),
            ['id'],
            ['solarsystem_id', 'constellation_id', 'region_id', 'name', 'type_id', 'group_id', 'parent_id', 'updated_at'],
            'Seeding Stars'
        );
    }

    /**
     * Transform star data for database insertion
     *
     * @param  array<int, array{constellationID: int, regionID: int, name: string}>  $solarsystemsLookup
     * @return array{id: int, solarsystem_id: int, constellation_id: int, region_id: int, name: string, type_id: int, group_id: int, parent_id: null, created_at: Carbon, updated_at: Carbon}|null
     */
    private function transformStar(StarDto $dto, array $solarsystemsLookup): ?array
    {
        if (! isset($solarsystemsLookup[$dto->solarSystemId])) {
            return null;
        }

        $sys = $solarsystemsLookup[$dto->solarSystemId];
        $starName = UniverseHelpers::generateStarName($sys['name']);

        return [
            'id' => $dto->id,
            'solarsystem_id' => $dto->solarSystemId,
            'constellation_id' => $sys['constellationID'],
            'region_id' => $sys['regionID'],
            'name' => $starName,
            'type_id' => $dto->typeId,
            'group_id' => 6, // Star group
            'parent_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Seed planets and build lookup map for moons and stations
     *
     * @param  array<int, array{constellationID: int, regionID: int, name: string}>  $solarsystemsLookup
     * @return array<int, array{celestialIndex: int}>
     */
    protected function seedPlanets(array $solarsystemsLookup): array
    {
        $celestialClass = ClassResolver::celestial();
        $planetsLookup = [];

        $this->streamUpsert(
            $celestialClass::query(),
            JSONL::lazy(Storage::path('sde/mapPlanets.jsonl'), PlanetDto::class),
            function (PlanetDto $planet) use ($solarsystemsLookup, &$planetsLookup) {
                return $this->transformPlanet($planet, $solarsystemsLookup, $planetsLookup);
            },
            ['id'],
            ['solarsystem_id', 'constellation_id', 'region_id', 'name', 'type_id', 'group_id', 'parent_id', 'updated_at'],
            'Seeding Planets'
        );

        return $planetsLookup;
    }

    /**
     * Transform planet data for database insertion
     *
     * @param  array<int, array{constellationID: int, regionID: int, name: string}>  $solarsystemsLookup
     * @param  array<int, array{celestialIndex: int}>  $planetsLookup
     * @return array{id: int, solarsystem_id: int, constellation_id: int, region_id: int, name: string, type_id: int, group_id: int, parent_id: null, created_at: Carbon, updated_at: Carbon}|null
     */
    private function transformPlanet(PlanetDto $dto, array $solarsystemsLookup, array &$planetsLookup): ?array
    {
        if (! isset($solarsystemsLookup[$dto->solarSystemId])) {
            return null;
        }

        $sys = $solarsystemsLookup[$dto->solarSystemId];
        $planetName = UniverseHelpers::generatePlanetName($sys['name'], $dto->celestialIndex);

        $planetsLookup[$dto->id] = [
            'celestialIndex' => $dto->celestialIndex,
        ];

        return [
            'id' => $dto->id,
            'solarsystem_id' => $dto->solarSystemId,
            'constellation_id' => $sys['constellationID'],
            'region_id' => $sys['regionID'],
            'name' => $planetName,
            'type_id' => $dto->typeId,
            'group_id' => 7, // Planet group
            'parent_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Seed moons and build lookup map for stations
     *
     * @param  array<int, array{constellationID: int, regionID: int, name: string}>  $solarsystemsLookup
     * @param  array<int, array{celestialIndex: int}>  $planetsLookup
     * @return array<int, array{orbitID: int, orbitIndex: int}>
     */
    protected function seedMoons(array $solarsystemsLookup, array $planetsLookup): array
    {
        $celestialClass = ClassResolver::celestial();
        $moonsLookup = [];

        $this->streamUpsert(
            $celestialClass::query(),
            JSONL::lazy(Storage::path('sde/mapMoons.jsonl'), MoonDto::class),
            function (MoonDto $moon) use ($solarsystemsLookup, $planetsLookup, &$moonsLookup) {
                return $this->transformMoon($moon, $solarsystemsLookup, $planetsLookup, $moonsLookup);
            },
            ['id'],
            ['solarsystem_id', 'constellation_id', 'region_id', 'name', 'type_id', 'group_id', 'parent_id', 'updated_at'],
            'Seeding Moons'
        );

        return $moonsLookup;
    }

    /**
     * Transform moon data for database insertion
     *
     * @param  array<int, array{constellationID: int, regionID: int, name: string}>  $solarsystemsLookup
     * @param  array<int, array{celestialIndex: int}>  $planetsLookup
     * @param  array<int, array{orbitID: int, orbitIndex: int}>  $moonsLookup
     * @return array{id: int, solarsystem_id: int, constellation_id: int, region_id: int, name: string, type_id: int, group_id: int, parent_id: int, created_at: Carbon, updated_at: Carbon}|null
     */
    private function transformMoon(MoonDto $dto, array $solarsystemsLookup, array $planetsLookup, array &$moonsLookup): ?array
    {
        if (! isset($solarsystemsLookup[$dto->solarSystemId]) || ! isset($planetsLookup[$dto->orbitId])) {
            return null;
        }

        $sys = $solarsystemsLookup[$dto->solarSystemId];
        $planet = $planetsLookup[$dto->orbitId];

        $planetName = UniverseHelpers::generatePlanetName($sys['name'], $planet['celestialIndex']);
        $moonName = UniverseHelpers::generateMoonName($planetName, $dto->orbitIndex);

        $moonsLookup[$dto->id] = [
            'orbitID' => $dto->orbitId,
            'orbitIndex' => $dto->orbitIndex,
        ];

        return [
            'id' => $dto->id,
            'solarsystem_id' => $dto->solarSystemId,
            'constellation_id' => $sys['constellationID'],
            'region_id' => $sys['regionID'],
            'name' => $moonName,
            'type_id' => $dto->typeId,
            'group_id' => 8, // Moon group
            'parent_id' => $dto->orbitId,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Seed asteroid belts
     *
     * @param  array<int, array{constellationID: int, regionID: int, name: string}>  $solarsystemsLookup
     * @param  array<int, array{celestialIndex: int}>  $planetsLookup
     * @return int Number of asteroid belts seeded
     */
    protected function seedAsteroidBelts(array $solarsystemsLookup, array $planetsLookup): int
    {
        $celestialClass = ClassResolver::celestial();

        return $this->streamUpsert(
            $celestialClass::query(),
            JSONL::lazy(Storage::path('sde/mapAsteroidBelts.jsonl'), AsteroidBeltDto::class),
            fn (AsteroidBeltDto $belt) => $this->transformAsteroidBelt($belt, $solarsystemsLookup, $planetsLookup),
            ['id'],
            ['solarsystem_id', 'constellation_id', 'region_id', 'name', 'type_id', 'group_id', 'parent_id', 'updated_at'],
            'Seeding Asteroid Belts'
        );
    }

    /**
     * Transform asteroid belt data for database insertion
     *
     * @param  array<int, array{constellationID: int, regionID: int, name: string}>  $solarsystemsLookup
     * @param  array<int, array{celestialIndex: int}>  $planetsLookup
     * @return array{id: int, solarsystem_id: int, constellation_id: int, region_id: int, name: string, type_id: null, group_id: int, parent_id: int, created_at: Carbon, updated_at: Carbon}|null
     */
    private function transformAsteroidBelt(AsteroidBeltDto $dto, array $solarsystemsLookup, array $planetsLookup): ?array
    {
        if (! isset($solarsystemsLookup[$dto->solarSystemId]) || ! isset($planetsLookup[$dto->orbitId])) {
            return null;
        }

        $sys = $solarsystemsLookup[$dto->solarSystemId];
        $planet = $planetsLookup[$dto->orbitId];

        $planetName = UniverseHelpers::generatePlanetName($sys['name'], $planet['celestialIndex']);
        $beltName = UniverseHelpers::generateAsteroidBeltName($planetName, $dto->orbitIndex);

        return [
            'id' => $dto->id,
            'solarsystem_id' => $dto->solarSystemId,
            'constellation_id' => $sys['constellationID'],
            'region_id' => $sys['regionID'],
            'name' => $beltName,
            'type_id' => null,
            'group_id' => 9, // Asteroid Belt group
            'parent_id' => $dto->orbitId,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Seed NPC stations
     *
     * @param  array<int, array{constellationID: int, regionID: int, name: string}>  $solarsystemsLookup
     * @param  array<int, array{celestialIndex: int}>  $planetsLookup
     * @param  array<int, array{orbitID: int, orbitIndex: int}>  $moonsLookup
     * @return int Number of stations seeded
     */
    protected function seedStations(array $solarsystemsLookup, array $planetsLookup, array $moonsLookup): int
    {
        $stationClass = ClassResolver::station();

        // Load auxiliary data for station names
        $corporationNames = collect();
        foreach (JSONL::lazy(Storage::path('sde/npcCorporations.jsonl'), CorporationDto::class) as $corporation) {
            $corporationNames->put($corporation->id, $corporation->name);
        }

        // Load station operations for station naming
        $operationNames = collect();
        foreach (JSONL::lazy(Storage::path('sde/stationOperations.jsonl'), StationOperationDto::class) as $operation) {
            $operationNames->put($operation->id, $operation->operationName);
        }

        return $this->streamUpsert(
            $stationClass::query(),
            JSONL::lazy(Storage::path('sde/npcStations.jsonl'), StationDto::class),
            fn (StationDto $station) => $this->transformStation($station, $solarsystemsLookup, $planetsLookup, $moonsLookup, $corporationNames, $operationNames),
            ['id'],
            ['solarsystem_id', 'constellation_id', 'region_id', 'name', 'type_id', 'parent_id', 'updated_at'],
            'Seeding Stations'
        );
    }

    /**
     * Transform station data for database insertion
     *
     * @param  array<int, array{constellationID: int, regionID: int, name: string}>  $solarsystemsLookup
     * @param  array<int, array{celestialIndex: int}>  $planetsLookup
     * @param  array<int, array{orbitID: int, orbitIndex: int}>  $moonsLookup
     * @param  Collection<int, string>  $corporationNames
     * @param  Collection<int, string>  $operationNames
     * @return array{id: int, solarsystem_id: int, constellation_id: int, region_id: int, name: string, type_id: int, parent_id: int|null, created_at: Carbon, updated_at: Carbon}|null
     */
    private function transformStation(
        StationDto $dto,
        array $solarsystemsLookup,
        array $planetsLookup,
        array $moonsLookup,
        Collection $corporationNames,
        Collection $operationNames
    ): ?array {
        if (! isset($solarsystemsLookup[$dto->solarSystemId])) {
            return null;
        }

        $sys = $solarsystemsLookup[$dto->solarSystemId];

        $orbitName = '';
        $parentId = null;

        if (isset($planetsLookup[$dto->orbitId])) {
            $planet = $planetsLookup[$dto->orbitId];
            $orbitName = UniverseHelpers::generatePlanetName($sys['name'], $planet['celestialIndex']);
            $parentId = $dto->orbitId;
        } elseif (isset($moonsLookup[$dto->orbitId])) {
            $moon = $moonsLookup[$dto->orbitId];
            // Need planet for moon name
            if (isset($planetsLookup[$moon['orbitID']])) {
                $planet = $planetsLookup[$moon['orbitID']];
                $planetName = UniverseHelpers::generatePlanetName($sys['name'], $planet['celestialIndex']);
                $orbitName = UniverseHelpers::generateMoonName($planetName, $moon['orbitIndex']);
                $parentId = $dto->orbitId;
            }
        }

        $corporationName = $corporationNames->get($dto->ownerId) ?? 'Unknown Corporation';

        $operationName = null;
        if ($dto->useOperationName && $dto->operationId !== null) {
            $operationName = $operationNames->get($dto->operationId);
        }

        $stationName = UniverseHelpers::generateStationName(
            $orbitName,
            $corporationName,
            $operationName,
            $dto->useOperationName
        );

        return [
            'id' => $dto->id,
            'solarsystem_id' => $dto->solarSystemId,
            'constellation_id' => $sys['constellationID'],
            'region_id' => $sys['regionID'],
            'name' => $stationName,
            'type_id' => $dto->typeId,
            'parent_id' => $parentId,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Seed stargates and their connections
     *
     * @param  array<int, array{constellationID: int, regionID: int, name: string}>  $solarsystemsLookup
     * @return array{int, int} Returns [stargate count, connection count]
     *
     * @throws Throwable
     */
    protected function seedStargatesAndConnections(array $solarsystemsLookup): array
    {
        return spin(fn () => $this->processStargates($solarsystemsLookup), 'Seeding Stargates and Connections');
    }

    /**
     * Process stargates and connections
     *
     * @param  array<int, array{constellationID: int, regionID: int, name: string}>  $solarsystemsLookup
     * @return array{int,int}
     *
     * @throws Throwable
     */
    private function processStargates(array $solarsystemsLookup): array
    {
        $stargatesBuffer = [];
        $connectionsBuffer = [];
        $stargateCount = 0;
        $connectionCount = 0;

        foreach (JSONL::lazy(Storage::path('sde/mapStargates.jsonl'), StargateDto::class) as $dto) {
            if (! isset($solarsystemsLookup[$dto->solarSystemId])) {
                continue;
            }

            $sys = $solarsystemsLookup[$dto->solarSystemId];

            // Buffer stargate record
            $stargatesBuffer[] = [
                'id' => $dto->id,
                'solarsystem_id' => $dto->solarSystemId,
                'destination_id' => $dto->destination->stargateId,
                'constellation_id' => $sys['constellationID'],
                'region_id' => $sys['regionID'],
                'position_x' => $dto->position->x,
                'position_y' => $dto->position->y,
                'position_z' => $dto->position->z,
                'type_id' => $dto->typeId,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Build connection record for this stargate
            $destSys = $solarsystemsLookup[$dto->destination->solarSystemId] ?? null;

            $connectionsBuffer[] = [
                'from_stargate_id' => $dto->id,
                'from_solarsystem_id' => $dto->solarSystemId,
                'from_region_id' => $sys['regionID'],
                'from_constellation_id' => $sys['constellationID'],
                'to_stargate_id' => $dto->destination->stargateId,
                'to_solarsystem_id' => $dto->destination->solarSystemId,
                'to_region_id' => $destSys['regionID'] ?? null,
                'to_constellation_id' => $destSys['constellationID'] ?? null,
                'is_regional' => isset($destSys) && $sys['regionID'] !== $destSys['regionID'],
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $stargateCount++;
            $connectionCount++;

            // Flush buffers when chunk size is reached
            if (count($stargatesBuffer) >= self::UPSERT_CHUNK_SIZE) {
                $this->flushStargateBuffers($stargatesBuffer, $connectionsBuffer);
                $stargatesBuffer = [];
                $connectionsBuffer = [];
            }
        }

        // Flush remaining stargates and connections
        if (! empty($stargatesBuffer)) {
            $this->flushStargateBuffers($stargatesBuffer, $connectionsBuffer);
        }

        return [$stargateCount, $connectionCount];
    }

    /**
     * Flush stargate and connection buffers to database
     *
     * @param  array<int, array<string, mixed>>  $stargatesBuffer
     * @param  array<int, array<string, mixed>>  $connectionsBuffer
     *
     * @throws Throwable
     */
    private function flushStargateBuffers(array $stargatesBuffer, array $connectionsBuffer): void
    {
        DB::transaction(function () use ($stargatesBuffer, $connectionsBuffer) {
            Schema::disableForeignKeyConstraints();
            Stargate::query()->upsert(
                $stargatesBuffer,
                ['id'],
                ['solarsystem_id', 'destination_id', 'constellation_id', 'region_id', 'position_x', 'position_y', 'position_z', 'type_id', 'updated_at']
            );
            SolarsystemConnection::query()->upsert(
                $connectionsBuffer,
                ['from_stargate_id'],
                ['from_solarsystem_id', 'from_region_id', 'from_constellation_id', 'to_stargate_id', 'to_solarsystem_id', 'to_region_id', 'to_constellation_id', 'is_regional', 'updated_at']
            );
            Schema::enableForeignKeyConstraints();
        });
    }
}
