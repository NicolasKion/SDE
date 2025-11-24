<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Data\Dto;

final readonly class StargateDestinationDto
{
    public function __construct(
        public int $stargateId,
        public int $solarSystemId,
    ) {}

    /**
     * @param  array{stargateID: int, solarSystemID: int}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            stargateId: $data['stargateID'],
            solarSystemId: $data['solarSystemID'],
        );
    }
}
