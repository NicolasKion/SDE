<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Data\Dto;

final readonly class UnitDto
{
    public function __construct(
        public int $id,
        public string $name,
        public string $description,
        public string $displayName,
    ) {}

    /**
     * @param  array{_key: int, name: string, description: array{en: string}|null, displayName: array{en: string}|null}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['_key'],
            name: $data['name'],
            description: $data['description']['en'] ?? '',
            displayName: $data['displayName']['en'] ?? '',
        );
    }
}
