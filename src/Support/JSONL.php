<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Support;

use Generator;
use RuntimeException;

use function method_exists;

class JSONL
{
    /**
     * Parse a JSONL file into an array.
     *
     * WARNING: This loads the entire file into memory. For large files,
     * use lazy() instead to stream the data.
     *
     * @template T
     *
     * @param  string  $file  Path to the JSONL file
     * @param  class-string<T>|null  $dtoClass  Optional DTO class with a fromArray() method
     * @return (T is null ? array<int,array<int,mixed>> : array<int,T>)
     */
    public static function parse(string $file, ?string $dtoClass = null): array
    {
        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if ($lines === false) {
            throw new RuntimeException("Could not read file: $file");
        }

        if ($dtoClass !== null) {
            if (! method_exists($dtoClass, 'fromArray')) {
                throw new RuntimeException("DTO class $dtoClass must have a fromArray() method");
            }
        }

        $data = [];
        foreach ($lines as $line) {
            $decoded = json_decode($line, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new RuntimeException('JSON decode error: '.json_last_error_msg());
            }

            if ($dtoClass !== null) {
                $decoded = $dtoClass::fromArray($decoded);
            }
            $data[] = $decoded;
        }

        return $data;
    }

    /**
     * Parse a JSONL file lazily using a generator.
     *
     * This method streams the file line-by-line, yielding one decoded object
     * at a time. This is memory-efficient for large files as only one line
     * is kept in memory at any given time.
     *
     * @template T
     *
     * @param  string  $file  Path to the JSONL file
     * @param  class-string<T>|null  $dtoClass  Optional DTO class with a fromArray() method
     * @return (T is null ? Generator<array<int,mixed>> : Generator<int,T>)
     */
    public static function lazy(string $file, ?string $dtoClass = null): Generator
    {
        $handle = fopen($file, 'r');

        if ($handle === false) {
            throw new RuntimeException("Could not open file: $file");
        }

        if ($dtoClass !== null) {
            if (! method_exists($dtoClass, 'fromArray')) {
                throw new RuntimeException("DTO class $dtoClass must have a fromArray() method");
            }
        }

        try {
            while (($line = fgets($handle)) !== false) {
                $line = trim($line);
                if ($line === '') {
                    continue;
                }

                $decoded = json_decode($line, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new RuntimeException('JSON decode error: '.json_last_error_msg());
                }

                if ($dtoClass !== null) {
                    yield $dtoClass::fromArray($decoded);
                } else {
                    yield $decoded;
                }
            }
        } finally {
            fclose($handle);
        }
    }
}
