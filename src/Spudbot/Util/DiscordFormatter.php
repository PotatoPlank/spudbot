<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Util;

use Carbon\Carbon;

class DiscordFormatter
{
    public static function mentionChannel(string $id): string
    {
        return "<#$id>";
    }

    public static function mentionRole(string $id): string
    {
        return "<@&$id>";
    }

    public static function mentionUser(string $id): string
    {
        return "<@$id>";
    }

    public static function boldText(string|int $text): string
    {
        return "**$text**";
    }

    public static function datetime(Carbon $date, $style = 'f'): string
    {
        return "<t:{$date->getTimestamp()}:$style>";
    }

    public static function customEmojiTag($name, string|int $id, $animated = false): string
    {
        return '<' . self::customEmoji($name, $id, $animated) . '>';
    }

    public static function customEmoji(string $name, string|int $id, $animated = false): string
    {
        $animatedTag = $animated ? 'a' : '';
        return "$animatedTag:$name:$id";
    }

}
