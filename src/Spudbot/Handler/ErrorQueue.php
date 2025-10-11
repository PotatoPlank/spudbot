<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024-2025. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Handler;


use Spudbot\Data\BotErrorDto;

class ErrorQueue
{
    private array $queue = [];

    public function __construct()
    {
        set_error_handler([$this, 'notify']);
    }

    public function addHandler(callable $handler): void
    {
        $this->queue[] = $handler;
    }

    public function notify(int $errorNumber, string $message, string $file, int $line, array $context): void
    {
        $dto = new BotErrorDto();
        $dto->number = $errorNumber;
        $dto->message = $message;
        $dto->file = $file;
        $dto->line = $line;
        $dto->context = $context;

        foreach ($this->queue as $handler) {
            $handler($dto);
        }
    }
}
