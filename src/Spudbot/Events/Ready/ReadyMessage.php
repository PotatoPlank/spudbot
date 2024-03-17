<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Events\Ready;

use Spudbot\Bot\Events;
use Spudbot\Events\AbstractEventSubscriber;

class ReadyMessage extends AbstractEventSubscriber
{
    public function update(): void
    {
        $this->spud->discord->getLogger()
            ->info('Booting complete.');
    }

    public function getEventName(): string
    {
        return Events::READY->value;
    }
}
