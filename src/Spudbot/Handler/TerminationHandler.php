<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Handler;

class TerminationHandler
{
    public const BOT_TERMINATION_MESSAGE = "Bot terminated.";

    public function handler(): void
    {
        print self::BOT_TERMINATION_MESSAGE . PHP_EOL;
        exit();
    }
}
