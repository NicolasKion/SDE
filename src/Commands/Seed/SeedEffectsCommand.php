<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Commands\Seed;

use Exception;
use Illuminate\Support\Facades\Storage;
use NicolasKion\SDE\ClassResolver;
use NicolasKion\SDE\Support\JSONL;

/**
 * @phpstan-type DogmaEffectsFile array{
 *    _key: int,
 *    disallowAutoRepeat: bool,
 *    effectCategoryID: int,
 *    electronicChance: bool,
 *    guid: string,
 *    isAssistance: bool,
 *    isOffensive: bool,
 *    isWarpSafe: bool,
 *    propulsionChance: bool,
 *    published: bool,
 *    rangeChance: bool,
 *    name: string,
 *    dischargeAttributeID?: int,
 *    durationAttributeID?: int,
 *    distribution?: int,
 *    falloffAttributeID?: int,
 *    rangeAttributeID?: int,
 *    trackingSpeedAttributeID?: int,
 *    iconID?: int,
 *    sfxName?: string,
 *    modifierInfo?: list<array{
 *      domain: string,
 *      func: string,
 *      modifiedAttributeID: int,
 *      modifyingAttributeID: int,
 *      operation: int,
 *      groupID?: int,
 *      skillTypeID?: int,
 *    }>,
 *    descriptionID?: array{
 *      de: string,
 *      en: string,
 *      es: string,
 *      fr: string,
 *      ja: string,
 *      ko: string,
 *      ru: string,
 *      zh: string
 *    },
 *    displayNameID?: array{
 *      de: string,
 *      en: string,
 *      es: string,
 *      fr: string,
 *      ja: string,
 *      ko: string,
 *      ru: string,
 *      zh: string
 *    }
 * }[]
 */
class SeedEffectsCommand extends BaseSeedCommand
{
    protected const string DOGMA_EFFECTS_FILE = 'sde/dogmaEffects.jsonl';

    protected $signature = 'sde:seed:effects';

    /**
     * @throws Exception
     */
    public function handle(): int
    {
        $this->ensureSDEExists();

        $this->info(sprintf('Parsing effects from %s', self::DOGMA_EFFECTS_FILE));

        /** @var DogmaEffectsFile $data */
        $data = JSONL::parse(Storage::path(self::DOGMA_EFFECTS_FILE));

        $effect = ClassResolver::effect();
        $effectModifier = ClassResolver::effectModifier();

        // Prepare effects data
        $effectsData = [];
        $modifiersData = [];

        foreach ($data as $item) {
            $effectsData[] = [
                'id' => $item['_key'],
                'name' => $item['name'],
                'description' => $item['descriptionID']['en'] ?? null,
                'icon_id' => $item['iconID'] ?? null,
                'sfx_name' => $item['sfxName'] ?? null,
                'published' => $item['published'] ?? true,
                'is_assistance' => $item['isAssistance'] ?? false,
                'is_offensive' => $item['isOffensive'] ?? false,
                'is_warp_safe' => $item['isWarpSafe'] ?? false,
                'discharge_attribute_id' => $item['dischargeAttributeID'] ?? null,
                'duration_attribute_id' => $item['durationAttributeID'] ?? null,
                'distribution' => $item['distribution'] ?? null,
                'falloff_attribute_id' => $item['falloffAttributeID'] ?? null,
                'range_attribute_id' => $item['rangeAttributeID'] ?? null,
                'tracking_speed_attribute_id' => $item['trackingSpeedAttributeID'] ?? null,
                'propulsion_chance' => $item['propulsionChance'] ?? false,
                'electronic_chance' => $item['electronicChance'] ?? false,
                'effect_category' => $item['effectCategoryID'] ?? null,
                'disallow_auto_repeat' => $item['disallowAutoRepeat'] ?? false,
                'display_name' => $item['displayNameID']['en'] ?? null,
                'post_expression' => $item['postExpression'] ?? null,
                'pre_expression' => $item['preExpression'] ?? null,
                'range_chance' => $item['rangeChance'] ?? false,
                'fitting_usage_chance_attribute_id' => $item['fittingUsageChanceAttributeID'] ?? null,
                'resistance_attribute_id' => $item['resistanceAttributeID'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Collect modifiers
            if (isset($item['modifierInfo'])) {
                foreach ($item['modifierInfo'] as $modifier) {
                    $modifiersData[] = [
                        'effect_id' => $item['_key'],
                        'domain' => $modifier['domain'] ?? null,
                        'func' => $modifier['func'] ?? null,
                        'modified_attribute_id' => $modifier['modifiedAttributeID'] ?? null,
                        'modifying_attribute_id' => $modifier['modifyingAttributeID'] ?? null,
                        'operator' => $modifier['operation'] ?? null,
                        'group_id' => $modifier['groupID'] ?? null,
                        'skill_type_id' => $modifier['skillTypeID'] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        $this->chunkedUpsert(
            $effect::query(),
            $effectsData,
            ['id'],
            ['name', 'description', 'icon_id', 'sfx_name', 'published', 'is_assistance', 'is_offensive', 'is_warp_safe', 'discharge_attribute_id', 'duration_attribute_id', 'distribution', 'falloff_attribute_id', 'range_attribute_id', 'tracking_speed_attribute_id', 'propulsion_chance', 'electronic_chance', 'effect_category', 'disallow_auto_repeat', 'display_name', 'post_expression', 'pre_expression', 'range_chance', 'fitting_usage_chance_attribute_id', 'resistance_attribute_id', 'updated_at']
        );

        if (! empty($modifiersData)) {
            $this->chunkedUpsert(
                $effectModifier::query(),
                $modifiersData,
                ['effect_id', 'domain', 'func', 'modified_attribute_id', 'modifying_attribute_id'],
                ['operator', 'group_id', 'skill_type_id', 'updated_at']
            );
        }

        $this->info(sprintf('Successfully seeded %d effects', count($data)));

        return self::SUCCESS;
    }
}
