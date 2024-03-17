<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Events\Message;


use Discord\Parts\Channel\Message;
use Discord\WebSockets\Event;
use Spudbot\Events\AbstractEventSubscriber;

class BotMentioned extends AbstractEventSubscriber
{
    public const REACT_ACKNOWLEDGE = ':grittywhat:1115440446114640013';

    public function getEventName(): string
    {
        return Event::MESSAGE_CREATE;
    }

    public function update(?Message $message = null): void
    {
        if (!$message) {
            return;
        }
        $botMentioned = $message->mentions->get('id', $this->spud->discord->application->id);
        if (!$botMentioned) {
            return;
        }
        $message->react(self::REACT_ACKNOWLEDGE);
    }
}
