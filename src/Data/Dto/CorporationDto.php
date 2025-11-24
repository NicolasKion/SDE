<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Data\Dto;

final readonly class CorporationDto
{
    public function __construct(
        public int $id,
        public ?int $stationId,
        public ?int $ceoId,
        public ?int $factionId,
        public int $shares,
        public float $taxRate,
        public ?string $tickerName,
        public string $name,
        public ?string $description,
    ) {}

    /**
     * @param  array{_key: int, stationID: int|null, ceoID: int|null, factionID: int|null, shares: int, taxRate: float, tickerName: string|null, name: array{en: string|null}, description: array{en: string|null}}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['_key'],
            stationId: $data['stationID'] ?? null,
            ceoId: $data['ceoID'] ?? null,
            factionId: $data['factionID'] ?? null,
            shares: $data['shares'],
            taxRate: $data['taxRate'],
            tickerName: $data['tickerName'] ?? null,
            name: $data['name']['en'] ?? '',
            description: $data['description']['en'] ?? null,
        );
    }
}
