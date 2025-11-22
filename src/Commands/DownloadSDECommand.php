<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Commands;

use Illuminate\Console\Command;
use Illuminate\Container\Attributes\Config;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

use function assert;
use function is_numeric;
use function Laravel\Prompts\spin;

class DownloadSDECommand extends Command
{
    protected const string SDE_LATEST_URL = 'https://developers.eveonline.com/static-data/tranquility/latest.jsonl';

    protected const string SDE_DATA_URL = 'https://developers.eveonline.com/static-data/tranquility/eve-online-static-data-%s-jsonl.zip';

    protected $signature = 'sde:download';

    public function handle(#[Config('sde.timeout', 300)] int $timeout): int
    {
        $build = spin($this->getLatestBuildNumber(...), 'Fetching latest SDE build number');

        $this->info(sprintf('Latest SDE build number is %s', $build));

        $sdeUrl = sprintf(self::SDE_DATA_URL, $build);

        spin(fn () => $this->downloadFile($sdeUrl, $timeout), 'Downloading SDE...');

        $extracted = spin(fn () => $this->extractSDE(), 'Extracting SDE...');

        if (! $extracted) {
            $this->error('Failed to extract SDE zip file.');

            return self::FAILURE;
        }

        $this->info('Successfully downloaded and extracted the SDE.');

        return self::SUCCESS;
    }

    /**
     * @throws ConnectionException
     */
    private function downloadFile(string $url, int $timeout): void
    {
        $response = Http::timeout($timeout)->get($url);

        Storage::put('sde/sde.zip', $response->getBody());
    }

    private function extractSDE(): bool
    {
        $zip = new ZipArchive;

        if (! $zip->open(Storage::path('sde/sde.zip'))) {
            return false;
        }

        $zip->extractTo(Storage::path('sde'));
        $zip->close();

        return true;
    }

    /**
     * @throws ConnectionException
     */
    private function getLatestBuildNumber(): ?int
    {
        $response = Http::get(self::SDE_LATEST_URL);
        if ($response->failed()) {
            return null;
        }

        $build = $response->json('buildNumber');
        if (! $build) {
            return null;
        }

        assert(is_numeric($build));

        return (int) $build;
    }
}
