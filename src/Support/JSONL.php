<?php

namespace NicolasKion\SDE\Support;

use RuntimeException;

class JSONL
{
    /**
     * Parse a JSONL file.
     *
     * @return array<int, mixed>
     */
    public static function parse(string $file): array
    {
        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if ($lines === false) {
            throw new RuntimeException("Could not read file: $file");
        }

        $data = [];
        foreach ($lines as $line) {
            $decoded = json_decode($line, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new RuntimeException('JSON decode error: '.json_last_error_msg());
            }
            $data[] = $decoded;
        }

        return $data;
    }
}
