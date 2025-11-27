<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Data\Dto;

final readonly class SolarsystemDto
{
    public function __construct(
        public int $id,
        public string $name,
        public int $constellationId,
        public int $regionId,
        public float $securityStatus,
        public PositionDto $position,
        public ?Position2dDto $position2d,
        public ?float $radius,
        public ?string $securityClass,
    ) {}

    /**
     * @param  array{_key: int, name: array{en: string}, constellationID: int, regionID: int, securityStatus: float, position: array{x: float, y: float, z: float}, position2D: array{x: float, y: float, z: float}|null, radius: float|null, securityClass: string|null}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['_key'],
            name: $data['name']['en'],
            constellationId: $data['constellationID'],
            regionId: $data['regionID'],
            securityStatus: $data['securityStatus'],
            position: PositionDto::fromArray($data['position']),
            position2d: Position2dDto::fromArray($data['position2D'] ?? null),
            radius: $data['radius'] ?? null,
            securityClass: $data['securityClass'] ?? null,
        );
    }
}
