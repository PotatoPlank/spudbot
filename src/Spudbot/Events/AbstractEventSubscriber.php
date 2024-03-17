<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Events;

use Spudbot\Bot\AbstractSubscriber;

abstract class AbstractEventSubscriber extends AbstractSubscriber
{
    public function hook(): void
    {
        $this->spud->eventObserver->subscribe($this->getEventName(), function (...$args) {
            if ($this->canRun(...$args)) {
                $this->update(...$args);
            }
        });
    }

    abstract public function getEventName(): string;
}
