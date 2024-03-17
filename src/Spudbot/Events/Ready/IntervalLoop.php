<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Events\Ready;

use Spudbot\Bot\Events;
use Spudbot\Events\AbstractEventSubscriber;

class IntervalLoop extends AbstractEventSubscriber
{

    public function getEventName(): string
    {
        return Events::READY->value;
    }

    public function update(): void
    {
        $this->spud->discord->getLoop()->addPeriodicTimer(60, function () {
            $this->spud->discord->emit(Events::EVERY_MINUTE->value);
        });
    }
}
