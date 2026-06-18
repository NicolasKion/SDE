# SDE Parser for EVE Online

[![Latest Version on Packagist](https://img.shields.io/packagist/v/nicolaskion/sde.svg?style=flat-square)](https://packagist.org/packages/nicolaskion/sde)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/nicolaskion/sde/tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/nicolaskion/sde/actions?query=workflow%3ATests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/nicolaskion/sde/formats.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/nicolaskion/sde/actions?query=workflow%3AFormats+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/nicolaskion/sde.svg?style=flat-square)](https://packagist.org/packages/nicolaskion/sde)

This package downloads EVE Online's Static Data Export (SDE) and seeds it into your Laravel application's database as Eloquent models.

## Requirements

- PHP 8.3+
- Laravel 12 or 13

## Installation

You can install the package via composer:

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

If you want to extend or override the bundled Eloquent models, publish them to your application and point the `sde.models` config entries at your own classes:

```bash
php artisan vendor:publish --tag="sde-models"
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

`sde:seed` orchestrates the individual seeders (icons, units, attributes, market groups, meta groups, categories, groups, graphics, races, effects, types, type attributes, type effects, the universe and more). Each one is also available on its own, e.g.:

```bash
php artisan sde:seed:types
php artisan sde:seed:universe
```

## Testing

```bash
composer test
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
