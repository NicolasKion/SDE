<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Data\Dto;

final readonly class PlanetDto
{
    /**
     * @param  array<int>  $moonIds
     * @param  array<int>  $asteroidBeltIds
     */
    public function __construct(
        public int $id,
        public int $solarSystemId,
        public int $celestialIndex,
        public int $orbitId,
        public array $moonIds,
        public array $asteroidBeltIds,
        public int $typeId,
    ) {}

    /**
     * @param  array{_key: int, solarSystemID: int, celestialIndex: int, orbitID: int, moonIDs: array<int>, asteroidBeltIDs: array<int>, typeID: int}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['_key'],
            solarSystemId: $data['solarSystemID'],
            celestialIndex: $data['celestialIndex'],
            orbitId: $data['orbitID'],
            moonIds: $data['moonIDs'] ?? [],
            asteroidBeltIds: $data['asteroidBeltIDs'] ?? [],
            typeId: $data['typeID'],
        );
    }
}
