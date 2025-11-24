<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Data\Dto;

final readonly class StarDto
{
    public function __construct(
        public int $id,
        public int $solarSystemId,
        public int $typeId,
    ) {}

    /**
     * @param  array{_key: int, solarSystemID: int, typeID: int}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['_key'],
            solarSystemId: $data['solarSystemID'],
            typeId: $data['typeID'],
        );
    }
}
