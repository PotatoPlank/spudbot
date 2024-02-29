<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Handler;

use Spudbot\Exception\BotTerminationException;
use Throwable;

use function Sentry\captureException;
use function Sentry\init;

class SentryExceptions
{
    public const CONSOLE_MESSAGE = "An exception was encountered and the bot stopped:";

    public function __construct(string $dsn, string $environment)
    {
        init(['dsn' => $dsn, 'environment' => $environment]);
    }

    public function handler(Throwable $exception): void
    {
        if (!$exception instanceof BotTerminationException || !empty($exception->getMessage())) {
            captureException($exception);
            $details = " {$exception->getFile()}:{$exception->getLine()} {$exception->getMessage()}";
            print self::CONSOLE_MESSAGE . " $details" . PHP_EOL;
        }
    }
}
