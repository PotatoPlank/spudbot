<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Handler;


use Throwable;

class ExceptionQueue
{
    private array $queue = [];

    public function __construct()
    {
        set_exception_handler([$this, 'notify']);
    }

    public function addHandler(callable $handler): void
    {
        $this->queue[] = $handler;
    }

    public function notify(Throwable $throwable): void
    {
        foreach ($this->queue as $handler) {
            $handler($throwable);
        }
    }
}
