<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Data\Dto;

final readonly class TypeDto
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $description,
        public int $groupId,
        public ?int $marketGroupId,
        public bool $published,
        public ?float $mass,
        public ?float $volume,
        public ?float $capacity,
        public ?float $portionSize,
        public ?float $basePrice,
        public ?float $radius,
        public ?int $iconId,
        public ?int $graphicId,
        public ?int $metaGroupId,
        public ?int $factionId,
    ) {}

    /**
     * @param  array{_key: int, name: array{en: string|null}, description: array{en: string|null}, groupID: int, marketGroupID: int|null, published: bool|null, mass: float|null, volume: float|null, capacity: float|null, portionSize: float|null, basePrice: float|null, radius: float|null, iconID: int|null, graphicID: int|null, metaGroupID: int|null, factionID: int|null}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['_key'],
            name: $data['name']['en'] ?? '',
            description: $data['description']['en'] ?? null,
            groupId: $data['groupID'],
            marketGroupId: $data['marketGroupID'] ?? null,
            published: $data['published'] ?? true,
            mass: $data['mass'] ?? null,
            volume: $data['volume'] ?? null,
            capacity: $data['capacity'] ?? null,
            portionSize: $data['portionSize'] ?? null,
            basePrice: $data['basePrice'] ?? null,
            radius: $data['radius'] ?? null,
            iconId: $data['iconID'] ?? null,
            graphicId: $data['graphicID'] ?? null,
            metaGroupId: $data['metaGroupID'] ?? null,
            factionId: $data['factionID'] ?? null,
        );
    }
}
