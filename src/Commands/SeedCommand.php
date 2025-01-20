<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Commands;

use Illuminate\Console\Command;

class SeedCommand extends Command
{
    protected $signature = 'sde:seed';

    public function handle(): int
    {

        $this->call('sde:seed:icons');
        $this->call('sde:seed:units');
        $this->call('sde:seed:attributes');
        $this->call('sde:seed:market-groups');
        $this->call('sde:seed:meta-groups');
        $this->call('sde:seed:categories');
        $this->call('sde:seed:groups');
        $this->call('sde:seed:graphics');
        $this->call('sde:seed:races');
        $this->call('sde:seed:types');
        $this->call('sde:seed:type-attributes');
        $this->call('sde:seed:universe');
        $this->call('sde:seed:socials');
        return self::SUCCESS;
    }
}
