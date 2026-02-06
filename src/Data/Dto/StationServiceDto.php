<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Data\Dto;

final readonly class StationServiceDto
{
    public function __construct(
        public int $id,
        public string $serviceName,
    ) {}

    /**
     * @param  array{_key: int, serviceName: array{en: string|null}}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['_key'],
            serviceName: $data['serviceName']['en'] ?? '',
        );
    }
}
