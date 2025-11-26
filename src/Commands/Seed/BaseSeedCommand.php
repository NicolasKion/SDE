<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Commands\Seed;

use Exception;
use Generator;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

use function Laravel\Prompts\info;
use function Laravel\Prompts\note;
use function Laravel\Prompts\progress;
use function Laravel\Prompts\spin;
use function Laravel\Prompts\table;

abstract class BaseSeedCommand extends Command
{
    const int PROGRESS_STEP = 100;

    const int UPSERT_CHUNK_SIZE = 1000; // Safe chunk size to avoid placeholder limits

    /** @var string Cache key for storing command statistics */
    protected const string CACHE_KEY = 'sde:seed:stats';

    /** @var int Cache duration in seconds (24 hours) */
    protected const int CACHE_TTL = 86400;

    protected $signature = 'sde:seed:type';

    /** @var int Starting memory usage in bytes */
    protected int $startMemory = 0;

    /** @var int Peak memory usage in bytes */
    protected int $peakMemory = 0;

    /** @var float Command start time */
    protected float $startTime = 0;

    /**
     * Initialize memory tracking
     */
    protected function startMemoryTracking(): void
    {
        $this->startMemory = memory_get_usage(true);
        $this->peakMemory = $this->startMemory;
        $this->startTime = microtime(true);

        if (! $this->isQuiet()) {
            info(sprintf('Starting with %s memory allocated', $this->formatBytes($this->startMemory)));
        }
    }

    /**
     * Check if quiet mode is enabled
     */
    protected function isQuiet(): bool
    {
        return $this->option('quiet') === true;
    }

    /**
     * Format bytes to human-readable string
     */
    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, 2).' '.$units[$pow];
    }

    /**
     * Log current memory usage
     */
    protected function logMemoryUsage(string $label = 'Current'): void
    {
        if ($this->isQuiet()) {
            return;
        }

        $current = memory_get_usage(true);
        $peak = memory_get_peak_usage(true);
        $this->peakMemory = max($this->peakMemory, $peak);

        $delta = $current - $this->startMemory;
        $sign = $delta >= 0 ? '+' : '';

        note(
            sprintf(
                'Current: %s | Peak: %s | Delta: %s%s',
                $this->formatBytes($current),
                $this->formatBytes($peak),
                $sign,
                $this->formatBytes($delta)
            ),
            $label
        );
    }

    /**
     * Display final memory statistics and save to cache
     */
    protected function displayMemoryStats(?int $recordCount = null): void
    {
        $final = memory_get_usage(true);
        $peak = memory_get_peak_usage(true);
        $delta = $final - $this->startMemory;
        $duration = microtime(true) - $this->startTime;

        $efficiency = $delta < 50 * 1024 * 1024 ? '✓ Excellent' : ($delta < 100 * 1024 * 1024 ? '○ Good' : '✗ High');

        // Always save stats to cache (never display per-command table)
        $this->saveCommandStats([
            'command' => $this->getName() ?? class_basename($this),
            'start_memory' => $this->startMemory,
            'final_memory' => $final,
            'peak_memory' => $peak,
            'delta_memory' => $delta,
            'duration' => $duration,
            'efficiency' => $efficiency,
            'record_count' => $recordCount,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Save command statistics to cache
     *
     * @param  array<string, mixed>  $stats
     */
    protected function saveCommandStats(array $stats): void
    {
        $allStats = Cache::get(self::CACHE_KEY, []);
        $allStats[$stats['command']] = $stats;
        Cache::put(self::CACHE_KEY, $allStats, self::CACHE_TTL);
    }

    /**
     * Clear all cached command statistics
     */
    protected function clearCommandStats(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Display summary of all seeded commands
     */
    protected function displayAllCommandStats(): void
    {
        $stats = $this->getAllCommandStats();

        if (empty($stats) || count($stats) <= 1) {
            return;
        }

        $this->newLine();
        info('Seed Commands Summary');

        $rows = [];
        $totalDuration = 0;
        $totalRecords = 0;
        $maxPeak = 0;

        foreach ($stats as $commandName => $data) {
            $rows[] = [
                str_replace('sde:seed:', '', $commandName),
                $data['record_count'] ? number_format($data['record_count']) : '-',
                $this->formatBytes($data['peak_memory']),
                $this->formatBytes($data['delta_memory']),
                sprintf('%.2fs', $data['duration']),
            ];

            $totalDuration += $data['duration'];
            if ($data['record_count']) {
                $totalRecords += $data['record_count'];
            }
            $maxPeak = max($maxPeak, $data['peak_memory']);
        }

        $rows[] = ['', '', '', '', ''];
        $rows[] = [
            'TOTAL',
            $totalRecords > 0 ? number_format($totalRecords) : '-',
            $this->formatBytes($maxPeak),
            '-',
            sprintf('%.2fs', $totalDuration),
        ];

        table(
            ['Command', 'Records', 'Peak Memory', 'Delta', 'Duration'],
            $rows
        );
    }

    /**
     * Get all cached command statistics
     *
     * @return array<string, array<string, mixed>>
     */
    protected function getAllCommandStats(): array
    {
        return Cache::get(self::CACHE_KEY, []);
    }

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
        $quiet = $this->isQuiet();

        // Only show progress for larger datasets (more than 1 chunk) and not in quiet mode
        if ($totalChunks > 1 && $label && ! $quiet) {
            $progressBar = progress(label: $label, steps: $totalItems);
            $progressBar->start();

            foreach ($chunks as $chunk) {
                $progressBar->advance(count($chunk));
                $query->upsert($chunk, $uniqueColumns, $updateColumns);
            }

            $progressBar->finish();
        } elseif ($label && ! $quiet) {
            // Use spin for single chunk operations
            spin(
                fn () => $query->upsert($data, $uniqueColumns, $updateColumns),
                $label
            );
        } else {
            // No label or quiet mode, just do the upsert
            foreach ($chunks as $chunk) {
                $query->upsert($chunk, $uniqueColumns, $updateColumns);
            }
        }
    }

    /**
     * Perform streamed upsert to keep memory usage low.
     *
     * This method processes a generator stream, transforming items one at a time
     * and buffering them for chunked database upserts. This keeps memory usage
     * constant regardless of the source data size.
     *
     * @param  Builder  $query  The Eloquent query builder for the target model
     * @param  Generator  $dataGenerator  Generator that yields items to process
     * @param  callable  $transformer  Callback to transform each item into a DB record.
     *                                 Should return an array or null to skip the item.
     *                                 Signature: function($item): ?array
     * @param  array<int,string>  $uniqueColumns  Columns to use for conflict detection
     * @param  array<int,string>  $updateColumns  Columns to update on conflict
     * @param  string|null  $label  Optional label for spinner
     * @param  bool  $showMemory  Whether to show memory usage during processing (ignored)
     * @return int Total number of items processed (excluding skipped items)
     */
    protected function streamUpsert(
        Builder $query,
        Generator $dataGenerator,
        callable $transformer,
        array $uniqueColumns,
        array $updateColumns,
        ?string $label = null,
        bool $showMemory = false
    ): int {
        if ($label) {
            return spin(
                fn () => $this->processStream($query, $dataGenerator, $transformer, $uniqueColumns, $updateColumns),
                $label
            );
        }

        return $this->processStream($query, $dataGenerator, $transformer, $uniqueColumns, $updateColumns);
    }

    /**
     * Process a stream of data and perform chunked upserts
     *
     * @param  array<int,string>  $uniqueColumns
     * @param  array<int,string>  $updateColumns
     */
    private function processStream(
        Builder $query,
        Generator $dataGenerator,
        callable $transformer,
        array $uniqueColumns,
        array $updateColumns
    ): int {
        $buffer = [];
        $count = 0;
        $chunkCount = 0;

        foreach ($dataGenerator as $item) {
            $record = $transformer($item);

            // Allow transformer to skip items by returning null
            if ($record === null) {
                continue;
            }

            $buffer[] = $record;
            $count++;
            $chunkCount++;

            // Flush buffer when chunk size is reached
            if ($chunkCount >= self::UPSERT_CHUNK_SIZE) {
                $query->upsert($buffer, $uniqueColumns, $updateColumns);
                $buffer = [];
                $chunkCount = 0;
            }
        }

        // Flush remaining items
        if (! empty($buffer)) {
            $query->upsert($buffer, $uniqueColumns, $updateColumns);
        }

        return $count;
    }
}
