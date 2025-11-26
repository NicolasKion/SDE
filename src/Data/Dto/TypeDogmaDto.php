<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Data\Dto;

final readonly class TypeDogmaDto
{
    /**
     * @param  array<TypeEffectDto>  $dogmaEffects
     */
    public function __construct(
        public int $typeId,
        public array $dogmaEffects,
    ) {}

    /**
     * @param  array{_key: int, dogmaEffects: array<int, array{effectID: int, isDefault: bool}>}  $data
     */
    public static function fromArray(array $data): self
    {
        $effects = [];
        foreach ($data['dogmaEffects'] ?? [] as $effect) {
            $effects[] = TypeEffectDto::fromArray($effect);
        }

        return new self(
            typeId: $data['_key'],
            dogmaEffects: $effects,
        );
    }
}
