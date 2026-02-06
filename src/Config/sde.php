<?php

declare(strict_types=1);

use NicolasKion\SDE\Models\Alliance;
use NicolasKion\SDE\Models\Attribute;
use NicolasKion\SDE\Models\Bloodline;
use NicolasKion\SDE\Models\Category;
use NicolasKion\SDE\Models\Celestial;
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
use NicolasKion\SDE\Models\OperationService;
use NicolasKion\SDE\Models\Race;
use NicolasKion\SDE\Models\Region;
use NicolasKion\SDE\Models\Service;
use NicolasKion\SDE\Models\Solarsystem;
use NicolasKion\SDE\Models\SolarsystemConnection;
use NicolasKion\SDE\Models\Stargate;
use NicolasKion\SDE\Models\Station;
use NicolasKion\SDE\Models\StationOperation;
use NicolasKion\SDE\Models\Type;
use NicolasKion\SDE\Models\TypeAttribute;
use NicolasKion\SDE\Models\Unit;

return [
    'models' => [
        'Attribute' => Attribute::class,
        'Category' => Category::class,
        'Celestial' => Celestial::class,
        'Constellation' => Constellation::class,
        'Graphic' => Graphic::class,
        'Group' => Group::class,
        'Icon' => Icon::class,
        'MarketGroup' => MarketGroup::class,
        'MetaGroup' => MetaGroup::class,
        'Race' => Race::class,
        'Region' => Region::class,
        'Solarsystem' => Solarsystem::class,
        'Station' => Station::class,
        'Type' => Type::class,
        'TypeAttribute' => TypeAttribute::class,
        'Unit' => Unit::class,
        'Alliance' => Alliance::class,
        'Corporation' => Corporation::class,
        'Bloodline' => Bloodline::class,
        'Faction' => Faction::class,
        'Effect' => Effect::class,
        'EffectModifier' => EffectModifier::class,
        'Stargate' => Stargate::class,
        'SolarsystemConnection' => SolarsystemConnection::class,
        'StationOperation' => StationOperation::class,
        'Service' => Service::class,
        'OperationService' => OperationService::class,
    ],
];
