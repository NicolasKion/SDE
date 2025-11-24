<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Data\Dto;

final readonly class RegionDto
{
    /**
     * @param  array<int>  $constellationIds
     */
    public function __construct(
        public int $id,
        public string $name,
        public array $constellationIds,
    ) {}

    /**
     * @param  array{_key: int, name: array{en: string|null}, constellationIDs: array<int>}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['_key'],
            name: $data['name']['en'] ?? '',
            constellationIds: $data['constellationIDs'],
        );
    }
}
