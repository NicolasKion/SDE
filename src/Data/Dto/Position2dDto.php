<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Data\Dto;

final readonly class Position2dDto
{
    public function __construct(
        public float $x,
        public float $y,
    ) {}

    /**
     * @param  array{x: float, y: float, z: float}|null  $data
     */
    public static function fromArray(?array $data): ?self
    {
        if ($data === null) {
            return null;
        }

        return new self(
            x: $data['x'],
            y: $data['y'],
        );
    }
}
