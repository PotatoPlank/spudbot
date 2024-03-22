<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Parsers\Sesh;

use Carbon\Carbon;
use Discord\Parts\Channel\Message;

class SeshValidator
{
    public static function isValidEmbed(Message $message): bool
    {
        $isSesh = $message->components->count() > 0 && $message->user_id === '616754792965865495';
        $isEvent = str_contains($message->components[0]->components[0]->custom_id, 'event_rsvp');
        return $isSesh && $isEvent;
    }

    public static function getTimestampFromSeshString(string $seshTimestampString): ?Carbon
    {
        if (!preg_match('/<t:(.*?):F>/', $seshTimestampString, $match)) {
            return null;
        }

        return Carbon::createFromTimestamp($match[1]);
    }

    public static function getCleanMembersFieldValue(string $fieldValue): string
    {
        if (str_starts_with($fieldValue, '>>> ')) {
            $fieldValue = substr($fieldValue, 4);
        }
        return trim($fieldValue);
    }

    public static function getCleanGroupName(string $fieldName): string
    {
        return trim(preg_replace('/\s\(\d(.*?)\)/', '', $fieldName));
    }
}
