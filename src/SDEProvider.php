<?php

namespace NicolasKion\SDE;

use Illuminate\Support\ServiceProvider;
use NicolasKion\SDE\Commands\DownloadSDECommand;
use NicolasKion\SDE\Commands\PrepareSDECommand;
use NicolasKion\SDE\Commands\Seed\SeedAttributesCommand;
use NicolasKion\SDE\Commands\Seed\SeedCategoriesCommand;
use NicolasKion\SDE\Commands\Seed\SeedGraphicsCommand;
use NicolasKion\SDE\Commands\Seed\SeedGroupsCommand;
use NicolasKion\SDE\Commands\Seed\SeedIconsCommand;
use NicolasKion\SDE\Commands\Seed\SeedMarketGroupsCommand;
use NicolasKion\SDE\Commands\Seed\SeedMetaGroupsCommand;
use NicolasKion\SDE\Commands\Seed\SeedRacesCommand;
use NicolasKion\SDE\Commands\Seed\SeedSocialsCommand;
use NicolasKion\SDE\Commands\Seed\SeedTypeAttributesCommand;
use NicolasKion\SDE\Commands\Seed\SeedTypesCommand;
use NicolasKion\SDE\Commands\Seed\SeedUnitsCommand;
use NicolasKion\SDE\Commands\Seed\SeedUniverseCommand;
use NicolasKion\SDE\Commands\SeedCommand;

class SDEProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishesMigrations([
            __DIR__ . '/migrations' => database_path('migrations')
        ], 'sde-migrations');

        $this->publishes([
            __DIR__ . '/Models' => app_path('Models')
        ], 'sde-models');

        $this->publishes([
            __DIR__ . '/Config/sde.php' => config_path('sde.php')
        ], 'sde-config');

        if ($this->app->runningInConsole()) {
            $this->commands([
                DownloadSDECommand::class,
                PrepareSDECommand::class,
                SeedCommand::class,
                SeedIconsCommand::class,
                SeedUnitsCommand::class,
                SeedAttributesCommand::class,
                SeedMarketGroupsCommand::class,
                SeedMetaGroupsCommand::class,
                SeedCategoriesCommand::class,
                SeedGroupsCommand::class,
                SeedGraphicsCommand::class,
                SeedRacesCommand::class,
                SeedTypesCommand::class,
                SeedTypeAttributesCommand::class,
                SeedUniverseCommand::class,
                SeedSocialsCommand::class,
            ]);
        }
    }
}
