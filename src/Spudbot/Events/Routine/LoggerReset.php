<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023-2025. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Events\Routine;


use Spudbot\Bot\Events;
use Spudbot\Events\AbstractEventSubscriber;

class LoggerReset extends AbstractEventSubscriber
{

    public function getEventName(): string
    {
        return Events::EVERY_TEN_MINUTES->value;
    }

    public function update(): void
    {
        $this->spud->logger->debug('Resetting logger.');
        $this->spud->logger->reset();
        $this->spud->logger->debug('Logger reset.');
    }

}
