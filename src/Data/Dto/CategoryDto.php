<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Data\Dto;

final readonly class CategoryDto
{
    public function __construct(
        public int $id,
        public string $name,
        public bool $published,
    ) {}

    /**
     * @param  array{_key: int, name: array{en:string|null}, published: bool|null}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['_key'],
            name: $data['name']['en'] ?? '',
            published: $data['published'] ?? true,
        );
    }
}
