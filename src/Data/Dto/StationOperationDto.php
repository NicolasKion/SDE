<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Data\Dto;

final readonly class StationOperationDto
{
    public function __construct(
        public int $id,
        public string $operationName,
    ) {}

    /**
     * @param  array{_key: int, operationName: array{en: string|null}}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['_key'],
            operationName: $data['operationName']['en'] ?? '',
        );
    }
}
