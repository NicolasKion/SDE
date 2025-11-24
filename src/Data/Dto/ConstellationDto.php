<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Data\Dto;

final readonly class ConstellationDto
{
    /**
     * @param  array<int>  $solarSystemIds
     */
    public function __construct(
        public int $id,
        public int $regionId,
        public string $name,
        public array $solarSystemIds,
    ) {}

    /**
     * @param  array{_key: int, regionID: int, name: array{en: string|null}, solarSystemIDs: array<int>}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['_key'],
            regionId: $data['regionID'],
            name: $data['name']['en'] ?? '',
            solarSystemIds: $data['solarSystemIDs'],
        );
    }
}
