<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Interface;

abstract class AbstractEventSubscriber extends AbstractSubscriber
{
    public function hook(): void
    {
        $this->spud->eventObserver->subscribe($this->getEventName(), [$this, 'update']);
    }

    abstract public function getEventName(): string;
}
