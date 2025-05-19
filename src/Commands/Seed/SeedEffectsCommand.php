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

        /** @var DogmaEffectsFile $data */
        $data = Yaml::parseFile(Storage::path($file));

        $effect = ClassResolver::effect();
        $effectModifier = ClassResolver::effectModifier();

        foreach ($data as $id => $values) {
            $effect::query()->updateOrCreate(['id' => $id], [
                'name' => $values['effectName'],
                'description' => $values['descriptionID']['en'] ?? null,
                'icon_id' => $values['iconID'] ?? null,
                'sfx_name' => $values['sfxName'] ?? null,
                'published' => $values['published'] ?? true,
                'is_assistance' => $values['isAssistance'] ?? false,
                'is_offensive' => $values['isOffensive'] ?? false,
                'is_warp_safe' => $values['isWarpSafe'] ?? false,
                'discharge_attribute_id' => $values['dischargeAttributeID'] ?? null,
                'duration_attribute_id' => $values['durationAttributeID'] ?? null,
                'distribution' => $values['distribution'] ?? null,
                'falloff_attribute_id' => $values['falloffAttributeID'] ?? null,
                'range_attribute_id' => $values['rangeAttributeID'] ?? null,
                'tracking_speed_attribute_id' => $values['trackingSpeedAttributeID'] ?? null,
                'propulsion_chance' => $values['propulsionChance'] ?? false,
                'electronic_chance' => $values['electronicChance'] ?? false,
                'effect_category' => $values['effectCategory'] ?? null,
                'disallow_auto_repeat' => $values['disallowAutoRepeat'] ?? false,
                'display_name' => $values['displayName'] ?? null,
                'post_expression' => $values['postExpression'] ?? null,
                'pre_expression' => $values['preExpression'] ?? null,
                'range_chance' => $values['rangeChance'] ?? false,
                'fitting_usage_chance_attribute_id' => $values['fittingUsageChanceAttributeID'] ?? null,
                'resistance_attribute_id' => $values['resistanceAttributeID'] ?? null,
            ]);

            if (isset($values['modifierInfo'])) {
                foreach ($values['modifierInfo'] as $modifier) {
                    $effectModifier::query()->updateOrCreate([
                        'effect_id' => $id,
                        'domain' => $modifier['domain'] ?? null,
                        'func' => $modifier['func'] ?? null,
                        'modified_attribute_id' => $modifier['modifiedAttributeID'] ?? null,
                        'modifying_attribute_id' => $modifier['modifyingAttributeID'] ?? null,
                        'operator' => $modifier['operation'] ?? null,
                        'group_id' => $modifier['groupID'] ?? null,
                        'skill_type_id' => $modifier['skillTypeID'] ?? null,
                    ]);
                }
            }
        }


        return self::SUCCESS;
    }
}
