<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023-2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Events\Message;


use Discord\Parts\Channel\Message;
use Discord\WebSockets\Event;
use Spudbot\Interface\AbstractEventSubscriber;
use Spudbot\Model\Channel;
use Spudbot\Model\Thread;

class LogThreadActivity extends AbstractEventSubscriber
{
    public function getEventName(): string
    {
        return Event::MESSAGE_CREATE;
    }

    public function update(?Message $message = null): void
    {
        if (!$message || $message->thread) {
            return;
        }

        $guild = $this->spud->guildRepository
            ->findByPart($message->guild);

        try {
            $channel = $this->spud->channelRepository
                ->findByPart($message->thread->parent);
        } catch (\OutOfBoundsException $exception) {
            $channel = new Channel();
            $channel->setDiscordId($message->thread->parent->id);
            $channel->setGuild($guild);
            $this->spud->channelRepository->save($channel);
        }

        try {
            $thread = $this->spud->threadRepository
                ->findByPart($message->thread);
        } catch (\OutOfBoundsException $exception) {
            $thread = new Thread();
            $thread->setDiscordId($message->thread->id);
            $thread->setGuild($guild);
            $thread->setChannel($channel);
        }
        $this->spud->threadRepository->save($thread);
    }
}
