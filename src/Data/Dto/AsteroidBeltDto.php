<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Data\Dto;

final readonly class AsteroidBeltDto
{
    public function __construct(
        public int $id,
        public int $solarSystemId,
        public int $celestialIndex,
        public int $orbitId,
        public int $orbitIndex,
    ) {}

    /**
     * @param  array{_key: int, solarSystemID: int, celestialIndex: int, orbitID: int, orbitIndex: int}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['_key'],
            solarSystemId: $data['solarSystemID'],
            celestialIndex: $data['celestialIndex'],
            orbitId: $data['orbitID'],
            orbitIndex: $data['orbitIndex'],
        );
    }
}
