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
        public ?float $radius,
        public ?string $securityClass,
    ) {}

    /**
     * @param  array{_key: int, name: array{en: string}, constellationID: int, regionID: int, securityStatus: float, position: array{x: float, y: float, z: float}, radius: float|null, securityClass: string|null}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['_key'],
            name: $data['name']['en'] ?? '',
            constellationId: $data['constellationID'],
            regionId: $data['regionID'],
            securityStatus: $data['securityStatus'],
            position: PositionDto::fromArray($data['position']),
            radius: $data['radius'] ?? null,
            securityClass: $data['securityClass'] ?? null,
        );
    }
}
