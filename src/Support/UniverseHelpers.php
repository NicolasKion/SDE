<?php

declare(strict_types=1);

namespace NicolasKion\SDE\Support;

class UniverseHelpers
{
    /**
     * Determine the area type based on ID ranges.
     * Based on: https://developers.eveonline.com/docs/guides/id-ranges/#stations
     *
     * @return 'eve'|'wh'|'abyssal'|'void'|'hidden'
     */
    public static function determineAreaType(int $id): string
    {
        // Regions: 10M-10.9M (eve), 11M-11.9M (wh), 12M-12.9M (abyssal), 14M-14.9M (void), 19M-19.9M (hidden)
        if ($id >= 10_000_000 && $id < 11_000_000) {
            return 'eve';
        }
        if ($id >= 11_000_000 && $id < 12_000_000) {
            return 'wh';
        }
        if ($id >= 12_000_000 && $id < 13_000_000) {
            return 'abyssal';
        }
        if ($id >= 14_000_000 && $id < 15_000_000) {
            return 'void';
        }
        if ($id >= 19_000_000 && $id < 20_000_000) {
            return 'hidden';
        }

        // Constellations: 20M-20.9M (eve), 21M-21.9M (wh), 22M-22.9M (abyssal), 24M-24.9M (void), 26M-26.9M (hidden)
        if ($id >= 20_000_000 && $id < 21_000_000) {
            return 'eve';
        }
        if ($id >= 21_000_000 && $id < 22_000_000) {
            return 'wh';
        }
        if ($id >= 22_000_000 && $id < 23_000_000) {
            return 'abyssal';
        }
        if ($id >= 24_000_000 && $id < 25_000_000) {
            return 'void';
        }
        if ($id >= 26_000_000 && $id < 27_000_000) {
            return 'hidden';
        }

        // Solar Systems: 30M-30.9M (eve), 31M-31.9M (wh), 32M-32.9M (abyssal), 34M-34.9M (void), 36M-36.9M (hidden)
        if ($id >= 30_000_000 && $id < 31_000_000) {
            return 'eve';
        }
        if ($id >= 31_000_000 && $id < 32_000_000) {
            return 'wh';
        }
        if ($id >= 32_000_000 && $id < 33_000_000) {
            return 'abyssal';
        }
        if ($id >= 34_000_000 && $id < 35_000_000) {
            return 'void';
        }
        if ($id >= 36_000_000 && $id < 37_000_000) {
            return 'hidden';
        }

        // Default to 'eve' if no match
        return 'eve';
    }

    /**
     * Generate star name.
     * Based on: https://developers.eveonline.com/docs/services/static-data/#celestial-names
     */
    public static function generateStarName(string $solarSystemName): string
    {
        return $solarSystemName;
    }

    /**
     * Generate planet name.
     * Format: <orbitName> <celestialIndex>
     */
    public static function generatePlanetName(string $orbitName, int $celestialIndex): string
    {
        return sprintf('%s %s', $orbitName, self::toRoman($celestialIndex));
    }

    /**
     * Convert number to Roman numerals.
     */
    private static function toRoman(int $number): string
    {
        $map = [
            1000 => 'M', 900 => 'CM', 500 => 'D', 400 => 'CD',
            100 => 'C', 90 => 'XC', 50 => 'L', 40 => 'XL',
            10 => 'X', 9 => 'IX', 5 => 'V', 4 => 'IV', 1 => 'I',
        ];

        $result = '';
        foreach ($map as $value => $numeral) {
            $count = intval($number / $value);
            $result .= str_repeat($numeral, $count);
            $number %= $value;
        }

        return $result;
    }

    /**
     * Generate moon name.
     * Format: <orbitName> - Moon <orbitIndex>
     */
    public static function generateMoonName(string $orbitName, int $orbitIndex): string
    {
        return sprintf('%s - Moon %s', $orbitName, self::toRoman($orbitIndex));
    }

    /**
     * Generate asteroid belt name.
     * Format: <orbitName> - Asteroid Belt <orbitIndex>
     */
    public static function generateAsteroidBeltName(string $orbitName, int $orbitIndex): string
    {
        return sprintf('%s - Asteroid Belt %s', $orbitName, self::toRoman($orbitIndex));
    }

    /**
     * Generate station name.
     * Format: <orbitName> - <corporationName> [<operationName>]
     * Based on: https://developers.eveonline.com/docs/services/static-data/#celestial-names
     */
    public static function generateStationName(
        ?string $orbitName,
        string $corporationName,
        ?string $operationName = null,
        bool $useOperationName = false
    ): string {

        $name = '';

        if ($orbitName !== null) {
            $name = "$orbitName -";
        }

        if ($useOperationName && $operationName !== null) {
            return "$orbitName $corporationName $operationName";
        }

        return "$name $corporationName";

    }

    /**
     * Generate stargate name.
     * Format: Stargate (<solarSystemName>)
     */
    public static function generateStargateName(string $solarSystemName): string
    {
        return sprintf('Stargate (%s)', $solarSystemName);
    }
}
