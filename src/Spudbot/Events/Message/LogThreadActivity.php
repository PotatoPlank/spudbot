<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023-2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Events\Message;


use DI\Attribute\Inject;
use Discord\Parts\Channel\Message;
use Discord\WebSockets\Event;
use Spudbot\Events\AbstractEventSubscriber;
use Spudbot\Services\ThreadService;

class LogThreadActivity extends AbstractEventSubscriber
{
    #[Inject]
    protected ThreadService $threadService;

    public function getEventName(): string
    {
        return Event::MESSAGE_CREATE;
    }

    public function update(?Message $message = null): void
    {
        if (!$message || !$message->thread) {
            return;
        }
        $this->spud->discord->getLogger()->info('Called thread activity.');

        $thread = $this->threadService->findOrCreateWithPart($message->thread);

        $this->threadService->save($thread);
    }
}
