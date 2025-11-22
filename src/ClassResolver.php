<?php

declare(strict_types=1);

namespace NicolasKion\SDE;

use NicolasKion\SDE\Models\Alliance;
use NicolasKion\SDE\Models\Attribute;
use NicolasKion\SDE\Models\Bloodline;
use NicolasKion\SDE\Models\Category;
use NicolasKion\SDE\Models\Celestial;
use NicolasKion\SDE\Models\Character;
use NicolasKion\SDE\Models\Constellation;
use NicolasKion\SDE\Models\Corporation;
use NicolasKion\SDE\Models\Effect;
use NicolasKion\SDE\Models\EffectModifier;
use NicolasKion\SDE\Models\Faction;
use NicolasKion\SDE\Models\Graphic;
use NicolasKion\SDE\Models\Group;
use NicolasKion\SDE\Models\Icon;
use NicolasKion\SDE\Models\MarketGroup;
use NicolasKion\SDE\Models\MetaGroup;
use NicolasKion\SDE\Models\Race;
use NicolasKion\SDE\Models\Region;
use NicolasKion\SDE\Models\Solarsystem;
use NicolasKion\SDE\Models\SolarsystemConnection;
use NicolasKion\SDE\Models\Stargate;
use NicolasKion\SDE\Models\Station;
use NicolasKion\SDE\Models\Type;
use NicolasKion\SDE\Models\TypeAttribute;
use NicolasKion\SDE\Models\TypeEffect;
use NicolasKion\SDE\Models\Unit;

class ClassResolver
{
    /**
     * @return class-string<Attribute>
     */
    public static function attribute(): string
    {
        return self::getClass(Attribute::class);
    }

    /**
     * @template T
     *
     * @param  class-string<T>  $class
     * @return class-string<T>
     */
    private static function getClass(string $class): string
    {
        $class_name = class_basename($class);

        /** @var class-string<T> $value */
        $value = config('sde.models.'.$class_name, $class);

        return $value;
    }

    /**
     * @return class-string<Category>
     */
    public static function category(): string
    {
        return self::getClass(Category::class);
    }

    /**
     * @return class-string<Graphic>
     */
    public static function graphic(): string
    {
        return self::getClass(Graphic::class);
    }

    /**
     * @return class-string<Group>
     */
    public static function group(): string
    {
        return self::getClass(Group::class);
    }

    /**
     * @return class-string<Icon>
     */
    public static function icon(): string
    {
        return self::getClass(Icon::class);
    }

    /**
     * @return class-string<MarketGroup>
     */
    public static function marketGroup(): string
    {
        return self::getClass(MarketGroup::class);
    }

    /**
     * @return class-string<MetaGroup>
     */
    public static function metaGroup(): string
    {
        return self::getClass(MetaGroup::class);
    }

    /**
     * @return class-string<Race>
     */
    public static function race(): string
    {
        return self::getClass(Race::class);
    }

    /**
     * @return class-string<TypeAttribute>
     */
    public static function typeAttribute(): string
    {
        return self::getClass(TypeAttribute::class);
    }

    /**
     * @return class-string<Type>
     */
    public static function type(): string
    {
        return self::getClass(Type::class);
    }

    /**
     * @return class-string<Unit>
     */
    public static function unit(): string
    {
        return self::getClass(Unit::class);
    }

    /**
     * @return class-string<Region>
     */
    public static function region(): string
    {
        return self::getClass(Region::class);
    }

    /**
     * @return class-string<Constellation>
     */
    public static function constellation(): string
    {
        return self::getClass(Constellation::class);
    }

    /**
     * @return class-string<Solarsystem>
     */
    public static function solarsystem(): string
    {
        return self::getClass(Solarsystem::class);
    }

    /**
     * @return class-string<Celestial>
     */
    public static function celestial(): string
    {
        return self::getClass(Celestial::class);
    }

    /**
     * @return class-string<Station>
     */
    public static function station(): string
    {
        return self::getClass(Station::class);
    }

    /**
     * @return class-string<Faction>
     */
    public static function faction(): string
    {
        return self::getClass(Faction::class);
    }

    /**
     * @return class-string<Bloodline>
     */
    public static function bloodline(): string
    {
        return self::getClass(Bloodline::class);
    }

    /**
     * @return class-string<Character>
     */
    public static function character(): string
    {
        return self::getClass(Character::class);
    }

    /**
     * @return class-string<Corporation>
     */
    public static function corporation(): string
    {
        return self::getClass(Corporation::class);
    }

    /**
     * @return class-string<Alliance>
     */
    public static function alliance(): string
    {
        return self::getClass(Alliance::class);
    }

    /**
     * @return class-string<Effect>
     */
    public static function effect(): string
    {
        return self::getClass(Effect::class);
    }

    /**
     * @return class-string<TypeEffect>
     */
    public static function typeEffect(): string
    {
        return self::getClass(TypeEffect::class);
    }

    /**
     * @return class-string<EffectModifier>
     */
    public static function effectModifier(): string
    {
        return self::getClass(EffectModifier::class);
    }

    /**
     * @return class-string<Stargate>
     */
    public static function stargate(): string
    {
        return self::getClass(Stargate::class);
    }

    /**
     * @return class-string<SolarsystemConnection>
     */
    public static function solarsystemConnection(): string
    {
        return self::getClass(SolarsystemConnection::class);
    }
}
