<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024-2025. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Handler;

use JetBrains\PhpStorm\NoReturn;
use Spudbot\Bot\SpudLogger;

class TerminationHandler
{
    public const string BOT_TERMINATION_MESSAGE = "Bot terminated.";

    #[NoReturn]
    public function handler(): void
    {
        SpudLogger::exception(self::BOT_TERMINATION_MESSAGE, true);
    }
}
