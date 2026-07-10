# SDE for Laravel

**EVE Online's Static Data Export as Eloquent models.**

[![Latest Version on Packagist](https://img.shields.io/packagist/v/nicolaskion/sde.svg?style=flat-square)](https://packagist.org/packages/nicolaskion/sde)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/nicolaskion/sde/tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/nicolaskion/sde/actions?query=workflow%3ATests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/nicolaskion/sde/formats.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/nicolaskion/sde/actions?query=workflow%3AFormats+branch%3Amain)
[![Supported PHP Versions](https://img.shields.io/packagist/dependency-v/nicolaskion/sde/php.svg?style=flat-square)](https://packagist.org/packages/nicolaskion/sde)
[![Total Downloads](https://img.shields.io/packagist/dt/nicolaskion/sde.svg?style=flat-square)](https://packagist.org/packages/nicolaskion/sde)
[![License](https://img.shields.io/packagist/l/nicolaskion/sde.svg?style=flat-square)](LICENSE.md)

This package downloads [EVE Online](https://www.eveonline.com)'s official [Static Data Export (SDE)](https://developers.eveonline.com/docs/services/sde/) from CCP and seeds it into your Laravel application's database — complete with migrations, Eloquent models, and relationships. Build market tools, fitting apps, universe maps, or killboards on top of a fully queryable SDE without ever touching a YAML file.

## What's included

| Domain | Data |
| --- | --- |
| **Items & dogma** | Categories, groups, types, meta groups, market groups, attributes, effects, units — including per-type attributes and effects |
| **Universe** | Regions, constellations, solar systems, stargates and their connections, celestials (stars, planets, moons, asteroid belts), stations with operations and services |
| **NPC & social** | Factions, races, bloodlines, NPC corporations — plus ready-made tables and models for alliances, corporations, and characters to fill from ESI |
| **Dynamic items** | Mutaplasmids, their applicable types, and attribute ranges |
| **Assets** | Icons and graphics metadata |

## Requirements

- PHP 8.3+ (tested up to 8.5, plus nightly)
- Laravel 12 or 13
- `ext-zip`

## Installation

Install the package via composer:

```bash
composer require nicolaskion/sde
```

Publish and run the migrations:

```bash
php artisan vendor:publish --tag="sde-migrations"
php artisan migrate
```

Optionally, publish the config file:

```bash
php artisan vendor:publish --tag="sde-config"
```

## Usage

Download the latest SDE from CCP:

```bash
php artisan sde:download
```

Seed everything into the database:

```bash
php artisan sde:seed
```

`sde:seed` orchestrates all individual seeders in dependency order. Each is also available on its own:

| Command | Seeds |
| --- | --- |
| `sde:seed:icons` | Icons |
| `sde:seed:graphics` | Graphics |
| `sde:seed:units` | Dogma units |
| `sde:seed:attributes` | Dogma attributes |
| `sde:seed:effects` | Dogma effects and modifiers |
| `sde:seed:categories` | Item categories |
| `sde:seed:groups` | Item groups |
| `sde:seed:meta-groups` | Meta groups (Tech I, Tech II, …) |
| `sde:seed:market-groups` | Market group tree |
| `sde:seed:races` | Races |
| `sde:seed:types` | Item types |
| `sde:seed:type-attributes` | Per-type dogma attributes |
| `sde:seed:type-effects` | Per-type dogma effects |
| `sde:seed:universe` | Regions, constellations, solar systems, stargates, celestials, stations |
| `sde:seed:socials` | Factions, bloodlines, NPC corporations |
| `sde:seed:dynamic-items` | Mutaplasmids |

### Querying the data

The models ship with their relationships wired up:

```php
use NicolasKion\SDE\Models\Solarsystem;
use NicolasKion\SDE\Models\Type;

// A ship with its group, market group and dogma attributes
$rifter = Type::query()
    ->with(['group', 'marketGroup', 'typeAttributes'])
    ->where('name', 'Rifter')
    ->first();

// All low-sec systems in a region, with their stations
$systems = Solarsystem::query()
    ->with(['constellation', 'stations'])
    ->whereBetween('security', [0.1, 0.45])
    ->whereRelation('region', 'name', 'Heimatar')
    ->get();
```

### Customizing the models

If you want to extend or override the bundled Eloquent models, publish them to your application and point the `sde.models` config entries at your own classes:

```bash
php artisan vendor:publish --tag="sde-models"
```

```php
// config/sde.php
'models' => [
    'Type' => App\Models\Type::class,
    // ...
],
```

The seeders resolve every model through this config, so your overrides are used everywhere.

### Keeping the data fresh

CCP updates the SDE with every game patch. To stay current, schedule the download and seed commands, e.g. in `routes/console.php`:

```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('sde:download')->weekly()->mondays()->at('11:30');
Schedule::command('sde:seed')->weekly()->mondays()->at('12:00');
```

## Testing

```bash
composer test
```

This runs Rector (dry-run), Pint, PHPStan (level max) and Pest.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently. Releases are automated with [release-please](https://github.com/googleapis/release-please) and published to [Packagist](https://packagist.org/packages/nicolaskion/sde).

## Credits

- [Nicolas Kion](https://github.com/NicolasKion)
- [All Contributors](../../contributors)

EVE Online and all related data are © [CCP hf.](https://www.ccpgames.com) This package is not affiliated with or endorsed by CCP.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
