<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Util;

use Discord\Parts\Channel\Channel;

class ChannelTypes
{
    public static function isChannel(int $type): bool
    {
        return !static::isThread($type);
    }

    public static function isThread(int $type): bool
    {
        return in_array($type, [
            Channel::TYPE_ANNOUNCEMENT_THREAD,
            Channel::TYPE_PUBLIC_THREAD,
            Channel::TYPE_PRIVATE_THREAD
        ], true);
    }
}
