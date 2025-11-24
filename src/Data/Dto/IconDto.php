<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Data\Dto;

final readonly class IconDto
{
    public function __construct(
        public int $id,
        public string $file,
    ) {}

    /**
     * @param  array{_key: int, iconFile: string}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['_key'],
            file: $data['iconFile'],
        );
    }
}
