<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Util;

use Spudbot\Helpers\Collection;

class Filesystem
{
    public static function fetchFilesByDirectoryRecursively(string $directoryPath): Collection
    {
        if (!is_dir($directoryPath)) {
            throw new \InvalidArgumentException('$directoryPath must be a valid directory.');
        }

        $result = new Collection();

        foreach (scandir($directoryPath) as $filename) {
            if ($filename[0] === '.') {
                continue;
            }

            $filePath = $directoryPath . DIRECTORY_SEPARATOR . $filename;
            if (is_dir($filePath)) {
                foreach (self::fetchFilesByDirectoryRecursively($filePath) as $childFilename) {
                    $result->push($filename . DIRECTORY_SEPARATOR . $childFilename);
                }
            } else {
                $result->push($filename);
            }
        }

        return $result;
    }

    public static function getNamespaceFromPath(string $filePath): string
    {
        $namespace = strstr($filePath, 'Spudbot');
        if (!$namespace) {
            throw new \InvalidArgumentException(
                'Unable to locate the expected namespace, please provide a valid path.'
            );
        }
        if (str_contains($namespace, '.php')) {
            $namespace = substr($namespace, 0, -4);
        }
        if (str_contains($namespace, '/')) {
            $namespace = str_replace('/', '\\', $namespace);
        }
        return $namespace;
    }
}