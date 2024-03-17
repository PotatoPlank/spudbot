<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Bot;

use Spudbot\Commands\AbstractCommandSubscriber;

class CommandObserver extends AbstractObserver
{
    public function hasCommand(string $commandName): bool
    {
        return $this->listeners->has(function (AbstractCommandSubscriber $command) use ($commandName) {
            return $command->getCommandName() === $commandName;
        });
    }
}
