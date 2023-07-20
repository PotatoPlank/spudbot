<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023. PotatoPlank <potatoplank@protonmail.com> 
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Util;

use Carbon\Carbon;

class Recurrence
{
    public const SECOND_UNIT = 'seconds';
    public const MINUTE_UNIT = 'minutes';
    public const HOUR_UNIT = 'hours';
    public const DAY_UNIT = 'days';
    public const WEEK_UNIT = 'weeks';
    public const MONTH_UNIT = 'months';
    public static array $defaultExcludedUnits = [
        self::SECOND_UNIT,
        self::MINUTE_UNIT,
        self::HOUR_UNIT,
    ];
    private static array $unitMap = [
        'second' => self::SECOND_UNIT,
        'seconds' => self::SECOND_UNIT,
        'sec' => self::SECOND_UNIT,
        'secs' => self::SECOND_UNIT,
        's' => self::SECOND_UNIT,
        'm' => self::MINUTE_UNIT,
        'min' => self::MINUTE_UNIT,
        'mins' => self::MINUTE_UNIT,
        'minute' => self::MINUTE_UNIT,
        'minutes' => self::MINUTE_UNIT,
        'hour' => self::HOUR_UNIT,
        'hours' => self::HOUR_UNIT,
        'hourly' => "1 " . self::HOUR_UNIT,
        'h' => self::HOUR_UNIT,
        'day' => self::DAY_UNIT,
        'days' => self::DAY_UNIT,
        'daily' => "1 " . self::DAY_UNIT,
        'd' => self::DAY_UNIT,
        'week' => self::WEEK_UNIT,
        'weeks' => self::WEEK_UNIT,
        'weekly' => "1 " . self::WEEK_UNIT,
        'bi-weekly' => "2 " . self::WEEK_UNIT,
        'w' => self::WEEK_UNIT,
        'wk' => self::WEEK_UNIT,
        'wks' => self::WEEK_UNIT,
        'month' => self::MONTH_UNIT,
        'months' => self::MONTH_UNIT,
        'monthly' => "1 " . self::MONTH_UNIT,
    ];

    /**
     * This provides rudimentary interval processing from user input to a more standardized format without involving some sort of NLP.
     * @param string $intervalString
     * @return string
     */
    public static function getIntervalFromString(string $intervalString): string
    {
        $intervalString = str_replace([',', '.'], '', trim($intervalString)); // Don't support decimals for intervals

        $intervalString = self::getStandardizedUnits($intervalString);
        $intervalString = self::removeFillerWords($intervalString);

        $intervalString = Numeric::wordsToNumber($intervalString);

        return $intervalString;
    }

    /**
     * Processed an interval string to standardize the relevant units
     * @param string $intervalString
     * @return string
     */
    private static function getStandardizedUnits(string $intervalString): string
    {
        $delimiters = [
            ' ',
            "\r",
            "\n",
        ];

        $part = '';
        $lastCharacter = '';
        $stringParts = [];
        $hasNumber = false;
        $hasUnit = false;
        foreach (str_split($intervalString) as $character) {
            if ($part !== '') {
                $isNumeralEnded = !is_numeric($character) && is_numeric($lastCharacter);
                if ($isNumeralEnded || in_array($character, $delimiters, true)) {
                    $stringParts[] = $part;
                    $part = '';
                }
            }
            if (!in_array($character, $delimiters, true)) {
                $part .= $character;
            }
            $lastCharacter = $character;

            // Set flags
            if (is_numeric($character)) {
                $hasNumber = true;
            }
        }
        if ($part !== '') {
            $stringParts[] = $part;
        }

        $parsedPieces = [];
        // Standardize units
        foreach ($stringParts as $stringPart) {
            if (isset(self::$unitMap[$stringPart])) {
                $stringPart = self::$unitMap[$stringPart];
                $hasUnit = true;
                if (!$hasNumber) {
                    $hasNumber = preg_match('~[0-9]+~', $stringPart) !== false;
                }
            }

            if (!empty($parsedPieces) && !is_numeric(end($parsedPieces)) && !is_numeric($stringPart)) {
                $parsedPieces[] = 'zero';
            }

            $parsedPieces[] = $stringPart;
        }

        if (!$hasNumber || !$hasUnit) {
            throw new \InvalidArgumentException("{$intervalString} is not a recognized interval.");
        }
        return implode(' ', $parsedPieces);
    }

    /**
     * Removes "filler" words commonly associated with an interval, but not needed here.
     * @param string $intervalString
     * @return string
     */
    private static function removeFillerWords(string $intervalString): string
    {
        if (!str_contains($intervalString, ' ')) {
            return $intervalString;
        }

        $words = [
            'every',
            'repeats',
            'and',
        ];

        $parts = explode(' ', $intervalString);
        foreach ($parts as $i => $part) {
            if (in_array($part, $words)) {
                unset($parts[$i]);
            }
        }

        return implode(' ', $parts);
    }

    public static function isIntervalLongEnough(string $intervalString, array $excludedUnits = []): bool
    {
        if (empty($excludedUnits)) {
            $excludedUnits = self::$defaultExcludedUnits;
        }

        foreach ($excludedUnits as $unit) {
            if (str_contains($intervalString, $unit)) {
                return false;
            }
        }

        return true;
    }

    public static function getNextDateTimeFromInterval(Carbon $dateTime, string $intervalString): Carbon
    {
        $intervalParts = explode(' ', $intervalString);
        foreach ($intervalParts as $i => $intervalPart) {
            if (is_numeric($intervalPart)) {
                $intervalParts[$i] = "+{$intervalPart}";
            }
        }
        $intervalString = implode(' ', $intervalParts);

        return $dateTime->copy()->modify($intervalString);
    }
}