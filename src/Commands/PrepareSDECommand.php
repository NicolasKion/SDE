<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class PrepareSDECommand extends Command
{
    protected $signature = 'sde:prepare';

    public function handle(): int
    {
        $sde_zip = 'sde/sde.zip';

        if (!Storage::fileExists($sde_zip)) {
            $this->error('Missing SDE! Download it first!');
        }

        $zip = new ZipArchive();

        $zip->open(Storage::path($sde_zip));

        $zip->extractTo(Storage::path('sde'));

        $zip->close();

        $this->stripEmptyStrings();

        $this->info('Successfully prepared SDE!');

        return self::SUCCESS;
    }

    /**
     * @throws Exception
     */
    private function stripEmptyStrings(string $file = 'sde/fsd/types.yaml'): void
    {
        $content = Storage::get($file);

        if (!$content) {
            throw new Exception('File not found!');
        }

        $content = (string)preg_replace('/["\']\s+["\']/', '', $content);

        Storage::put($file, $content);
    }
}
