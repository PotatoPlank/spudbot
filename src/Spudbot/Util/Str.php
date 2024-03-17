<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Util;

class Str
{
    public static function containsOnePhrase(string $haystack, array $phrases): bool
    {
        foreach ($phrases as $phrase) {
            if (str_contains($haystack, $phrase)) {
                return true;
            }
        }
        return false;
    }

    public static function hasSimilarWord(string $haystack, array $matches, int $threshold = 70): bool
    {
        $haystackWords = explode(' ', $haystack);
        foreach ($haystackWords as $word) {
            foreach ($matches as $match) {
                similar_text($word, $match, $percent);
                if ($percent > $threshold) {
                    return true;
                }
            }
        }
        return false;
    }

    public static function isSnakeCase(string $field): bool
    {
        return !str_starts_with($field, '_');
    }

    public static function snakeToCamelCase(string $field): string
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $field))));
    }

    public static function camelToSnakeCase(string $field): string
    {
        $pattern = '/(?<=\\w)(?=[A-Z])|(?<=[a-z])(?=[0-9])/';
        $snakeCase = preg_replace($pattern, '_', $field);
        return strtolower($snakeCase);
    }
}
