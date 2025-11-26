<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Commands\Seed;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use NicolasKion\SDE\ClassResolver;
use NicolasKion\SDE\Data\Dto\EffectDto;
use NicolasKion\SDE\Support\JSONL;

use function Laravel\Prompts\spin;

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
        $this->startMemoryTracking();

        $count = spin(fn () => $this->processEffects(), 'Seeding Effects and Modifiers');

        $this->displayMemoryStats($count);

        return self::SUCCESS;
    }

    /**
     * Process effects and their modifiers from JSONL file
     */
    private function processEffects(): int
    {
        $effect = ClassResolver::effect();
        $effectModifier = ClassResolver::effectModifier();

        $count = 0;
        $effectsBuffer = [];
        $modifiersBuffer = [];
        $count = 0;

        // Process effects and their modifiers together in one pass
        foreach (JSONL::lazy(Storage::path(self::DOGMA_EFFECTS_FILE), EffectDto::class) as $effect) {
            $effectsBuffer[] = [
                'id' => $effect->id,
                'name' => $effect->name,
                'description' => $effect->description,
                'icon_id' => $effect->iconId,
                'sfx_name' => $effect->sfxName,
                'published' => $effect->published,
                'is_assistance' => $effect->isAssistance,
                'is_offensive' => $effect->isOffensive,
                'is_warp_safe' => $effect->isWarpSafe,
                'discharge_attribute_id' => $effect->dischargeAttributeId,
                'duration_attribute_id' => $effect->durationAttributeId,
                'distribution' => $effect->distribution,
                'falloff_attribute_id' => $effect->falloffAttributeId,
                'range_attribute_id' => $effect->rangeAttributeId,
                'tracking_speed_attribute_id' => $effect->trackingSpeedAttributeId,
                'propulsion_chance' => $effect->propulsionChance,
                'electronic_chance' => $effect->electronicChance,
                'effect_category' => $effect->effectCategoryId,
                'disallow_auto_repeat' => $effect->disallowAutoRepeat,
                'display_name' => $effect->displayName,
                'post_expression' => $effect->postExpression,
                'pre_expression' => $effect->preExpression,
                'range_chance' => $effect->rangeChance,
                'fitting_usage_chance_attribute_id' => $effect->fittingUsageChanceAttributeId,
                'resistance_attribute_id' => $effect->resistanceAttributeId,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Collect modifiers for this effect
            foreach ($effect->modifierInfo as $modifier) {
                $modifiersBuffer[] = [
                    'effect_id' => $effect->id,
                    'domain' => $modifier->domain,
                    'func' => $modifier->func,
                    'modified_attribute_id' => $modifier->modifiedAttributeId,
                    'modifying_attribute_id' => $modifier->modifyingAttributeId,
                    'operator' => $modifier->operation,
                    'group_id' => $modifier->groupId,
                    'skill_type_id' => $modifier->skillTypeId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            $count++;

            // Flush buffers when chunk size is reached
            if (count($effectsBuffer) >= self::UPSERT_CHUNK_SIZE) {
                DB::transaction(function () use ($effect, $effectModifier, &$effectsBuffer, &$modifiersBuffer) {
                    $effect::upsert(
                        $effectsBuffer,
                        ['id'],
                        ['name', 'description', 'icon_id', 'sfx_name', 'published', 'is_assistance', 'is_offensive', 'is_warp_safe', 'discharge_attribute_id', 'duration_attribute_id', 'distribution', 'falloff_attribute_id', 'range_attribute_id', 'tracking_speed_attribute_id', 'propulsion_chance', 'electronic_chance', 'effect_category', 'disallow_auto_repeat', 'display_name', 'post_expression', 'pre_expression', 'range_chance', 'fitting_usage_chance_attribute_id', 'resistance_attribute_id', 'updated_at']
                    );

                    if (! empty($modifiersBuffer)) {
                        $effectModifier::upsert(
                            $modifiersBuffer,
                            ['effect_id', 'domain', 'func', 'modified_attribute_id', 'modifying_attribute_id'],
                            ['operator', 'group_id', 'skill_type_id', 'updated_at']
                        );
                    }
                });

                $effectsBuffer = [];
                $modifiersBuffer = [];
            }
        }

        // Flush remaining effects and modifiers
        if (! empty($effectsBuffer)) {
            DB::transaction(function () use ($effect, $effectModifier, $effectsBuffer, $modifiersBuffer) {
                $effect::upsert(
                    $effectsBuffer,
                    ['id'],
                    ['name', 'description', 'icon_id', 'sfx_name', 'published', 'is_assistance', 'is_offensive', 'is_warp_safe', 'discharge_attribute_id', 'duration_attribute_id', 'distribution', 'falloff_attribute_id', 'range_attribute_id', 'tracking_speed_attribute_id', 'propulsion_chance', 'electronic_chance', 'effect_category', 'disallow_auto_repeat', 'display_name', 'post_expression', 'pre_expression', 'range_chance', 'fitting_usage_chance_attribute_id', 'resistance_attribute_id', 'updated_at']
                );

                if (! empty($modifiersBuffer)) {
                    $effectModifier::upsert(
                        $modifiersBuffer,
                        ['effect_id', 'domain', 'func', 'modified_attribute_id', 'modifying_attribute_id'],
                        ['operator', 'group_id', 'skill_type_id', 'updated_at']
                    );
                }
            });
        }

        return $count;
    }
}
