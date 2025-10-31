<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024-2025. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Bot;

class ApplicationVersion
{
    /**
     * Update includes breaking changes
     */
    public const int MAJOR = 7;
    /**
     * Update includes non-breaking features
     */
    public const int MINOR = 0;
    /**
     * Updates includes non-breaking bugfixes
     */
    public const int REVISION = 0;
    public static string $buildNumber;

    public static function get(): string
    {
        $version = sprintf('v%d.%d.%d', self::MAJOR, self::MINOR, self::REVISION);
        $buildFile = dirname(__DIR__, 3) . '/.build-version';
        $jsonFile = dirname(__DIR__, 3) . '/build.json';

        if (!file_exists($jsonFile) && !file_exists($buildFile)) {
            return $version;
        }

        if (file_exists($buildFile)) {
            self::$buildNumber = file_get_contents($buildFile);
        } else {
            $contents = file_get_contents($jsonFile);
            $buildDetails = json_decode($contents, false, 512, JSON_THROW_ON_ERROR);
            self::$buildNumber = $buildDetails->date ?? '';
        }

        if (!empty(self::$buildNumber)) {
            $version .= ' - ' . self::$buildNumber;
        }

        return $version;
    }
}
