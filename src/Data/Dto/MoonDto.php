<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Data\Dto;

final readonly class MoonDto
{
    public function __construct(
        public int $id,
        public int $solarSystemId,
        public int $celestialIndex,
        public int $typeId,
        public int $orbitId,
        public int $orbitIndex,
    ) {}

    /**
     * @param  array{_key: int, solarSystemID: int, celestialIndex: int, typeID: int, orbitID: int, orbitIndex: int}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['_key'],
            solarSystemId: $data['solarSystemID'],
            celestialIndex: $data['celestialIndex'],
            typeId: $data['typeID'],
            orbitId: $data['orbitID'],
            orbitIndex: $data['orbitIndex'],
        );
    }
}
