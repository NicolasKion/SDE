<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Data\Dto;

final readonly class TypeEffectDto
{
    public function __construct(
        public int $effectId,
        public bool $isDefault,
    ) {}

    /**
     * @param  array{effectID: int, isDefault: bool}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            effectId: $data['effectID'],
            isDefault: $data['isDefault'],
        );
    }
}
