<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

use function Laravel\Prompts\info;
use function Laravel\Prompts\table;

class SeedCommand extends Command
{
    protected $signature = 'sde:seed';

    /** @var string Cache key for storing command statistics */
    protected const CACHE_KEY = 'sde:seed:stats';

    public function handle(): int
    {
        // Clear previous stats cache for fresh run
        Cache::forget(self::CACHE_KEY);

        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);

        try {
            Schema::disableForeignKeyConstraints();

            // Run all seed commands with --quiet flag
            $this->call('sde:seed:icons', ['--quiet' => true]);
            $this->call('sde:seed:units', ['--quiet' => true]);
            $this->call('sde:seed:attributes', ['--quiet' => true]);
            $this->call('sde:seed:market-groups', ['--quiet' => true]);
            $this->call('sde:seed:meta-groups', ['--quiet' => true]);
            $this->call('sde:seed:categories', ['--quiet' => true]);
            $this->call('sde:seed:groups', ['--quiet' => true]);
            $this->call('sde:seed:graphics', ['--quiet' => true]);
            $this->call('sde:seed:races', ['--quiet' => true]);
            $this->call('sde:seed:effects', ['--quiet' => true]);
            $this->call('sde:seed:types', ['--quiet' => true]);
            $this->call('sde:seed:type-attributes', ['--quiet' => true]);
            $this->call('sde:seed:type-effects', ['--quiet' => true]);
            $this->call('sde:seed:universe', ['--quiet' => true]);
            $this->call('sde:seed:socials', ['--quiet' => true]);

        } catch (Exception $e) {
            $this->error('An error occurred while seeding: '.$e->getMessage());

            return self::FAILURE;
        } finally {
            Schema::enableForeignKeyConstraints();
        }

        $elapsed = microtime(true) - $startTime;
        $finalMemory = memory_get_usage(true);
        $peakMemory = memory_get_peak_usage(true);
        // Display aggregate statistics
        $this->displayAggregateStats($elapsed, $startMemory, $finalMemory, $peakMemory);
        info('All seeds completed successfully ✓');

        return self::SUCCESS;
    }

    /**
     * Display aggregate statistics from all seed commands
     */
    protected function displayAggregateStats(float $elapsed, int $startMemory, int $finalMemory, int $peakMemory): void
    {
        $stats = Cache::get(self::CACHE_KEY, []);

        if (empty($stats)) {
            return;
        }

        $rows = [];
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
            sprintf('%.2fs', $elapsed),
        ];

        table(
            ['Command', 'Records', 'Peak Memory', 'Delta', 'Duration'],
            $rows
        );

        // Overall memory stats
        $this->newLine();
        table(
            ['Overall Metric', 'Value'],
            [
                ['Total Duration', sprintf('%.2f seconds', $elapsed)],
                ['Starting Memory', $this->formatBytes($startMemory)],
                ['Final Memory', $this->formatBytes($finalMemory)],
                ['Peak Memory', $this->formatBytes($peakMemory)],
                ['Memory Delta', $this->formatBytes($finalMemory - $startMemory)],
            ]
        );
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
}
