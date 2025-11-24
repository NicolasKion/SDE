<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Data\Dto;

final readonly class StationDto
{
    public function __construct(
        public int $id,
        public int $solarSystemId,
        public int $orbitId,
        public ?int $orbitIndex,
        public ?int $celestialIndex,
        public int $ownerId,
        public ?int $operationId,
        public bool $useOperationName,
        public int $typeId,
    ) {}

    /**
     * @param  array{_key: int, solarSystemID: int, orbitID: int, orbitIndex: int|null, celestialIndex: int|null, ownerID: int, operationID: int|null, useOperationName: bool, typeID: int}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['_key'],
            solarSystemId: $data['solarSystemID'],
            orbitId: $data['orbitID'],
            orbitIndex: $data['orbitIndex'] ?? null,
            celestialIndex: $data['celestialIndex'] ?? null,
            ownerId: $data['ownerID'],
            operationId: $data['operationID'] ?? null,
            useOperationName: $data['useOperationName'],
            typeId: $data['typeID'],
        );
    }
}
