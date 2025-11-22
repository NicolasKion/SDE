<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Commands\Seed;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

use function Laravel\Prompts\progress;

abstract class BaseSeedCommand extends Command
{
    const int PROGRESS_STEP = 100;

    const int UPSERT_CHUNK_SIZE = 1000; // Safe chunk size to avoid placeholder limits

    protected $signature = 'sde:seed:type';

    /**
     * @throws Exception
     */
    protected function ensureSDEExists(): void
    {
        if (! Storage::directoryExists('sde')) {
            throw new Exception('SDE not found!');
        }
    }

    /**
     * @param  array<int,mixed>  $data
     */
    protected function progress(string $label, int $total, callable $callback, array $data): void
    {
        $progress = progress(label: $label, steps: $total);

        $progress->start();

        $index = 0;
        $per_step = (int) ceil($total / self::PROGRESS_STEP);

        foreach ($data as $key => $item) {
            $index++;
            if ($index % $per_step === 0) {
                $progress->advance($per_step);
            }
            $callback($item, $key);
        }

        $progress->finish();
    }

    /**
     * Perform chunked upsert to avoid database placeholder limits with built-in progress tracking
     *
     * @param  array<int,mixed>  $data
     * @param  array<int,string>  $uniqueColumns
     * @param  array<int,string>  $updateColumns
     */
    protected function chunkedUpsert(Builder $query, array $data, array $uniqueColumns, array $updateColumns, ?string $label = null): void
    {
        if (empty($data)) {
            return;
        }

        $chunks = array_chunk($data, self::UPSERT_CHUNK_SIZE);
        $totalChunks = count($chunks);
        $totalItems = count($data);

        // Only show progress for larger datasets (more than 1 chunk)
        if ($totalChunks > 1 && $label) {
            $progress = progress(label: $label, steps: $totalItems);
            $progress->start();

            foreach ($chunks as $chunk) {
                $progress->advance(count($chunk));
                $query->upsert($chunk, $uniqueColumns, $updateColumns);
            }

            $progress->finish();
        } else {
            // For small datasets, just do the upsert without progress overhead
            foreach ($chunks as $chunk) {
                $query->upsert($chunk, $uniqueColumns, $updateColumns);
            }
        }
    }
}
