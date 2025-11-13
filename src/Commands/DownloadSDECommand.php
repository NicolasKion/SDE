<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class DownloadSDECommand extends Command
{
    protected $signature = 'sde:download';

    public function handle(): int
    {
        $response = Http::timeout(300)->get('https://eve-static-data-export.s3-eu-west-1.amazonaws.com/tranquility/sde.zip');

        if ($response->failed()) {
            $this->error('Failed to download SDE!');
            $this->error($response->body());

            return self::FAILURE;
        }

        Storage::put('sde/sde.zip', $response->getBody());

        $this->info('Successfully downloaded the SDE and saved it into storage');

        return self::SUCCESS;
    }
}
