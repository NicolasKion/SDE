<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Data\Dto;

final readonly class DynamicItemAttributeDto
{
    /**
     * @param  array<int, array{applicableTypes: int[], resultingType: int}>  $inputOutputMapping
     */
    public function __construct(
        public int $id,
        public array $inputOutputMapping,
    ) {}

    /**
     * @param  array{_key: int, inputOutputMapping?: array<int, array{applicableTypes: int[], resultingType: int}>}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['_key'],
            inputOutputMapping: $data['inputOutputMapping'] ?? [],
        );
    }
}
