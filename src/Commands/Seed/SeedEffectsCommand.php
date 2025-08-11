<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Commands\Seed;

use Exception;
use Illuminate\Support\Facades\Storage;
use NicolasKion\SDE\ClassResolver;
use Symfony\Component\Yaml\Yaml;

/**
 * @phpstan-type DogmaEffectsFile array<int,array{
 *    disallowAutoRepeat: bool,
 *    effectCategory: int,
 *    effectID: int,
 *    effectName: string,
 *    electronicChance: bool,
 *    guid: string,
 *    isAssistance: bool,
 *    isOffensive: bool,
 *    isWarpSafe: bool,
 *    propulsionChance: bool,
 *    published: bool,
 *    rangeChance: bool,
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
 *      operation: int
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
 * }>
 */
class SeedEffectsCommand extends BaseSeedCommand
{
    protected $signature = 'sde:seed:effects';

    /**
     * @throws Exception
     */
    public function handle(): int
    {
        $this->ensureSDEExists();

        $file = 'sde/fsd/dogmaEffects.yaml';

        $this->info(sprintf('Parsing effects from %s', $file));

        /** @var DogmaEffectsFile $data */
        $data = Yaml::parseFile(Storage::path($file));

        $effect = ClassResolver::effect();
        $effectModifier = ClassResolver::effectModifier();

        // Prepare effects data
        $effectsData = [];
        $modifiersData = [];

        foreach ($data as $key => $item) {
            $effectsData[] = [
                'id' => $key,
                'name' => $item['effectName'],
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
                'effect_category' => $item['effectCategory'] ?? null,
                'disallow_auto_repeat' => $item['disallowAutoRepeat'] ?? false,
                'display_name' => $item['displayName'] ?? null,
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
                        'effect_id' => $key,
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
