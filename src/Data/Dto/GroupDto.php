<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Data\Dto;

final readonly class GroupDto
{
    public function __construct(
        public int $id,
        public string $name,
        public int $categoryId,
        public bool $published,
        public bool $anchorable,
        public bool $fittableNonSingleton,
        public bool $useBasePrice,
    ) {}

    /**
     * @param  array{_key: int, name: array{en: string|null}, categoryID: int, published: bool|null, anchorable: bool|null, fittableNonSingleton: bool|null, useBasePrice: bool|null}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['_key'],
            name: $data['name']['en'] ?? '',
            categoryId: $data['categoryID'],
            published: $data['published'] ?? true,
            anchorable: $data['anchorable'] ?? false,
            fittableNonSingleton: $data['fittableNonSingleton'] ?? false,
            useBasePrice: $data['useBasePrice'] ?? false,
        );
    }
}
