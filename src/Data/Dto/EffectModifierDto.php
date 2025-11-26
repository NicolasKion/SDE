<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Data\Dto;

final readonly class EffectModifierDto
{
    public function __construct(
        public ?string $domain,
        public ?string $func,
        public ?int $modifiedAttributeId,
        public ?int $modifyingAttributeId,
        public ?int $operation,
        public ?int $groupId,
        public ?int $skillTypeId,
    ) {}

    /**
     * @param  array{domain: string, func: string, modifiedAttributeID: int, modifyingAttributeID: int, operation: int, groupID?: int, skillTypeID?: int}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            domain: $data['domain'] ?? null,
            func: $data['func'] ?? null,
            modifiedAttributeId: $data['modifiedAttributeID'] ?? null,
            modifyingAttributeId: $data['modifyingAttributeID'] ?? null,
            operation: $data['operation'] ?? null,
            groupId: $data['groupID'] ?? null,
            skillTypeId: $data['skillTypeID'] ?? null,
        );
    }
}
