<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Data\Dto;

final readonly class PositionDto
{
    public function __construct(
        public float $x,
        public float $y,
        public float $z,
    ) {}

    /**
     * @param  array{x: float, y: float, z: float}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            x: $data['x'],
            y: $data['y'],
            z: $data['z'],
        );
    }
}
