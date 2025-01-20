<?php

namespace NicolasKion\SDE\Commands\Seed;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

abstract class BaseSeedCommand extends Command
{
    protected $signature = 'sde:seed:type';

    protected function ensureSDEExists(): void
    {
        if (!Storage::directoryExists('sde')) {
            throw new Exception('SDE not found!');
        }
    }
}
