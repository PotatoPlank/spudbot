<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Bindable\Event\Message;


use Discord\Parts\Channel\Message;
use Discord\WebSockets\Event;
use Spudbot\Interface\IBindableEvent;
use Spudbot\Model\Channel;
use Spudbot\Model\Thread;

class LogThreadActivity extends IBindableEvent
{

    public function getBoundEvent(): string
    {
        return Event::MESSAGE_CREATE;
    }

    public function getListener(): callable
    {
        return function (Message $message) {
            if ($message->thread) {
                $guild = $this->spud->getGuildRepository()
                    ->findByPart($message->guild);

                try {
                    $channel = $this->spud->getChannelRepository()
                        ->findByPart($message->channel);
                } catch (\OutOfBoundsException $exception) {
                    $channel = new Channel();
                    $channel->setDiscordId($message->channel->id);
                    $channel->setGuild($guild);
                    $this->spud->getChannelRepository()->save($channel);
                }

                try {
                    $thread = $this->spud->getThreadRepository()
                        ->findByPart($message->thread);
                } catch (\OutOfBoundsException $exception) {
                    $thread = new Thread();
                    $thread->setDiscordId($message->thread->id);
                    $thread->setGuild($guild);
                    $thread->setChannel($channel);
                }
                $this->spud->getThreadRepository()->save($thread);
            }
        };
    }
}