<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Data\Dto;

final readonly class AttributeDto
{
    public function __construct(
        public int $id,
        public bool $highIsGood,
        public string $description,
        public float $defaultValue,
        public ?int $iconId,
        public bool $published,
        public string $displayName,
        public string $name,
        public bool $stackable,
        public ?int $unitId,
    ) {}

    /**
     * @param  array{_key: int, attributeCategoryID: int|null, dataType: int, highIsGood: null|bool, displayName: array{en: string|null}, defaultValue: null|int, iconID: int|null, published: bool|null, name: string|null, description: string|null, stackable: bool|null, unitID: int|null}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['_key'],
            highIsGood: $data['highIsGood'] ?? false,
            description: $data['description'] ?? '',
            defaultValue: $data['defaultValue'] ?? 0,
            iconId: $data['iconID'] ?? null,
            published: $data['published'] ?? false,
            displayName: $data['displayName']['en'] ?? '',
            name: $data['name'] ?? '',
            stackable: $data['stackable'] ?? false,
            unitId: $data['unitID'] ?? null,
        );
    }
}
