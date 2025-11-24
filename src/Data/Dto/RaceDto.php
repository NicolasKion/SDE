<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Data\Dto;

final readonly class RaceDto
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $description,
        public ?int $iconId,
    ) {}

    /**
     * @param  array{_key: int, name: array{en:string|null}, description: array{en: string|null}, iconID: int|null}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['_key'],
            name: $data['name']['en'] ?? '',
            description: $data['description']['en'] ?? null,
            iconId: $data['iconID'] ?? null,
        );
    }
}
