<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023-2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Events\Meme;


use Discord\Parts\Channel\Message;
use Discord\WebSockets\Event;
use Spudbot\Interface\AbstractEventSubscriber;
use Spudbot\Util\Str;

class SuperiorSteak extends AbstractEventSubscriber
{
    private string $reaction = ':bonk:1114416108385095700';
    private array $triggerKeywords = [
        'pats',
        'genos',
    ];

    public function getEventName(): string
    {
        return Event::MESSAGE_CREATE;
    }

    public function update(?Message $message = null): void
    {
        if (!$message) {
            return;
        }
        $mentionedSteak = stripos('steak', $message->content) !== false;
        $mentionedEateries = Str::hasSimilarWord($message->content, $this->triggerKeywords, 65);
        if (!$mentionedSteak || !$mentionedEateries) {
            return;
        }

        $message->react($this->reaction);
    }
}
