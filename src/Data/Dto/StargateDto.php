<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Data\Dto;

final readonly class StargateDto
{
    public function __construct(
        public int $id,
        public int $solarSystemId,
        public int $typeId,
        public PositionDto $position,
        public StargateDestinationDto $destination,
    ) {}

    /**
     * @param  array{_key: int, solarSystemID: int, typeID: int, position: array{x: float, y: float, z: float}, destination: array{stargateID: int, solarSystemID: int}}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['_key'],
            solarSystemId: $data['solarSystemID'],
            typeId: $data['typeID'],
            position: PositionDto::fromArray($data['position']),
            destination: StargateDestinationDto::fromArray($data['destination']),
        );
    }
}
