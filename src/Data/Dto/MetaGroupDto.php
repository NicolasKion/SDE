<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Data\Dto;

final readonly class MetaGroupDto
{
    public function __construct(
        public int $id,
        public ?string $name,
        public ?int $iconId,
        public ?string $iconSuffix,
        public ?string $description,
    ) {}

    /**
     * @param  array{_key: int, name: array{en:string|null}, iconID: int|null, iconSuffix: string|null, description: array{en:string|null}}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['_key'],
            name: $data['name']['en'] ?? null,
            iconId: $data['iconID'] ?? null,
            iconSuffix: $data['iconSuffix'] ?? null,
            description: $data['description']['en'] ?? null,
        );
    }
}
