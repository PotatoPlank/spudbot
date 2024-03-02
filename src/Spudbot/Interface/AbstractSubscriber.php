<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Interface;

use Spudbot\Bot\Spud;

abstract class AbstractSubscriber
{
    public function __construct(protected Spud $spud)
    {
    }

    abstract public function hook(): void;

    abstract public function update(): void;

    public function canRun(): bool
    {
        return true;
    }
}
