<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Data\Dto;

final readonly class GraphicDto
{
    public function __construct(
        public int $id,
        public ?string $file,
        public ?string $sofFactionName,
        public ?string $sofHullName,
        public ?string $sofRaceName,
    ) {}

    /**
     * @param  array{_key: int, iconFolder: string|null, sofFactionName: string|null, sofHullName: string|null, sofRaceName: string|null}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['_key'],
            file: $data['iconFolder'] ?? null,
            sofFactionName: $data['sofFactionName'] ?? null,
            sofHullName: $data['sofHullName'] ?? null,
            sofRaceName: $data['sofRaceName'] ?? null,
        );
    }
}
