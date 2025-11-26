<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Data\Dto;

final readonly class EffectDto
{
    /**
     * @param  array<EffectModifierDto>  $modifierInfo
     */
    public function __construct(
        public int $id,
        public bool $disallowAutoRepeat,
        public int $effectCategoryId,
        public bool $electronicChance,
        public ?string $guid,
        public bool $isAssistance,
        public bool $isOffensive,
        public bool $isWarpSafe,
        public bool $propulsionChance,
        public bool $published,
        public bool $rangeChance,
        public string $name,
        public ?int $dischargeAttributeId,
        public ?int $durationAttributeId,
        public ?int $distribution,
        public ?int $falloffAttributeId,
        public ?int $rangeAttributeId,
        public ?int $trackingSpeedAttributeId,
        public ?int $iconId,
        public array $modifierInfo,
        public ?string $description,
        public ?string $displayName,
        public ?int $fittingUsageChanceAttributeId,
        public ?int $resistanceAttributeId,
        public ?int $npcActivationChanceAttributeId,
        public ?int $npcUsageChanceAttributeId,
    ) {}

    /**
     * @param  array{_key: int, disallowAutoRepeat: bool, effectCategoryID: int, electronicChance: bool, guid?: string, isAssistance: bool, isOffensive: bool, isWarpSafe: bool, propulsionChance: bool, published: bool, rangeChance: bool, name: string, dischargeAttributeID?: int, durationAttributeID?: int, distribution?: int, falloffAttributeID?: int, rangeAttributeID?: int, trackingSpeedAttributeID?: int, iconID?: int, modifierInfo?: list<array{domain: string, func: string, modifiedAttributeID?: int, modifyingAttributeID?: int, operation?: int, groupID?: int, skillTypeID?: int, effectID?: int}>, description?: array{en?: string}, displayName?: array{en?: string}, fittingUsageChanceAttributeID?: int, resistanceAttributeID?: int, npcActivationChanceAttributeID?: int, npcUsageChanceAttributeID?: int}  $data
     */
    public static function fromArray(array $data): self
    {
        $modifiers = [];
        foreach ($data['modifierInfo'] ?? [] as $modifier) {
            $modifiers[] = EffectModifierDto::fromArray($modifier);
        }

        return new self(
            id: $data['_key'],
            disallowAutoRepeat: $data['disallowAutoRepeat'],
            effectCategoryId: $data['effectCategoryID'],
            electronicChance: $data['electronicChance'],
            guid: $data['guid'] ?? null,
            isAssistance: $data['isAssistance'],
            isOffensive: $data['isOffensive'],
            isWarpSafe: $data['isWarpSafe'],
            propulsionChance: $data['propulsionChance'],
            published: $data['published'],
            rangeChance: $data['rangeChance'],
            name: $data['name'],
            dischargeAttributeId: $data['dischargeAttributeID'] ?? null,
            durationAttributeId: $data['durationAttributeID'] ?? null,
            distribution: $data['distribution'] ?? null,
            falloffAttributeId: $data['falloffAttributeID'] ?? null,
            rangeAttributeId: $data['rangeAttributeID'] ?? null,
            trackingSpeedAttributeId: $data['trackingSpeedAttributeID'] ?? null,
            iconId: $data['iconID'] ?? null,
            modifierInfo: $modifiers,
            description: $data['description']['en'] ?? null,
            displayName: $data['displayName']['en'] ?? null,
            fittingUsageChanceAttributeId: $data['fittingUsageChanceAttributeID'] ?? null,
            resistanceAttributeId: $data['resistanceAttributeID'] ?? null,
            npcActivationChanceAttributeId: $data['npcActivationChanceAttributeID'] ?? null,
            npcUsageChanceAttributeId: $data['npcUsageChanceAttributeID'] ?? null,
        );
    }
}
