<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Data\Dto;

final readonly class MarketGroupDto
{
    public function __construct(
        public int $id,
        public ?int $parentId,
        public string $name,
        public string $description,
        public ?int $iconId,
        public bool $hasTypes,
    ) {}

    /**
     * @param  array{_key: int, parentGroupID: int|null, name: array{en: string|null}, description: array{en: null|string}, iconID: int|null, hasTypes: bool|null}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['_key'],
            parentId: $data['parentGroupID'] ?? null,
            name: $data['name']['en'] ?? '',
            description: $data['description']['en'] ?? '',
            iconId: $data['iconID'] ?? null,
            hasTypes: $data['hasTypes'] ?? true,
        );
    }
}
