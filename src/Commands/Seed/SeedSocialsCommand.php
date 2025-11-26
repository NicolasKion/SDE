<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Commands\Seed;

use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use NicolasKion\SDE\ClassResolver;
use NicolasKion\SDE\Data\Dto\CorporationDto;
use NicolasKion\SDE\Support\JSONL;

use function Laravel\Prompts\spin;

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
 * @phpstan-type CorporationsFile array{
 *     _key: int,
 *     stationID: int|null,
 *     ceoID: int|null,
 *     factionID: int|null,
 *     shares: int,
 *     taxRate: float,
 *     tickerName: string|null,
 *     name: array{en: string|null},
 *     description: array{en: string|null},
 * }[]
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
     * @throws ConnectionException|Exception
     */
    public function handle(): int
    {
        $this->ensureSDEExists();
        $this->startMemoryTracking();

        $factionCount = $this->seedFactions();
        $corpCount = $this->seedCorps();
        $bloodlineCount = $this->seedBloodlines();

        $totalCount = $factionCount + $corpCount + $bloodlineCount;

        $this->displayMemoryStats($totalCount);

        return self::SUCCESS;
    }

    private function seedFactions(): int
    {
        return spin(fn () => $this->fetchAndSeedFactions(), 'Fetching and seeding factions from ESI API');
    }

    /**
     * Fetch factions from ESI and seed them
     *
     * @throws ConnectionException
     */
    private function fetchAndSeedFactions(): int
    {
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
            ['name', 'description', 'corporation_id', 'is_unique', 'militia_corporation_id', 'size_factor', 'solarsystem_id', 'station_count', 'station_system_count', 'updated_at'],
            'Seeding Factions'
        );

        return count($upsertData);
    }

    /**
     * @throws Exception
     */
    private function seedCorps(): int
    {
        return spin(fn () => $this->processCorporations(), 'Processing corporations from SDE');
    }

    /**
     * Process corporations from SDE file
     */
    private function processCorporations(): int
    {
        $corp = ClassResolver::corporation();
        $char = ClassResolver::character();
        $station = ClassResolver::station();

        // First pass: collect CEO IDs
        $char_ids = [];
        foreach (JSONL::lazy(Storage::path('sde/npcCorporations.jsonl'), CorporationDto::class) as $dto) {
            if ($dto->ceoId !== null) {
                $char_ids[] = $dto->ceoId;
            }
        }

        // Create characters for CEOs
        $char::createFromIds(array_unique($char_ids));

        // Second pass: create corporations
        $upsertData = [];
        foreach (JSONL::lazy(Storage::path('sde/npcCorporations.jsonl'), CorporationDto::class) as $dto) {
            $stationData = $station::query()->find($dto->stationId);
            $upsertData[] = [
                'id' => $dto->id,
                'ceo_id' => $dto->ceoId,
                'creator_id' => null,
                'home_station_id' => $stationData->id ?? null,
                'faction_id' => $dto->factionId,
                'date_founded' => null,
                'url' => null,
                'name' => $dto->name,
                'description' => $dto->description,
                'member_count' => null,
                'npc' => true,
                'shares' => $dto->shares,
                'tax_rate' => $dto->taxRate,
                'war_eligible' => false,
                'ticker' => $dto->tickerName,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        $this->chunkedUpsert(
            $corp::query(),
            $upsertData,
            ['id'],
            ['ceo_id', 'creator_id', 'home_station_id', 'faction_id', 'date_founded', 'url', 'name', 'description', 'member_count', 'npc', 'shares', 'tax_rate', 'war_eligible', 'ticker', 'updated_at'],
            'Seeding Corporations'
        );

        return count($upsertData);
    }

    private function seedBloodlines(): int
    {
        return spin(fn () => $this->fetchAndSeedBloodlines(), 'Fetching and seeding bloodlines from ESI API');
    }

    /**
     * Fetch bloodlines from ESI and seed them
     *
     * @throws ConnectionException
     */
    private function fetchAndSeedBloodlines(): int
    {
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
            ['name', 'description', 'corporation_id', 'ship_type_id', 'race_id', 'intelligence', 'charisma', 'perception', 'memory', 'willpower', 'updated_at'],
            'Seeding Bloodlines'
        );

        return count($upsertData);
    }
}
