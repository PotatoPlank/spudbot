<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023-2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Events\Meme;


use Discord\Parts\Channel\Message;
use Discord\WebSockets\Event;
use Spudbot\Events\AbstractEventSubscriber;
use Spudbot\Util\Str;

class WhyyHelicopter extends AbstractEventSubscriber
{
    private string $whyy = ':whyy:1115394039815090196';
    private string $helicopter = '🚁';
    private array $triggerKeywords = [
        'why helicopter',
        'whyy',
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

        $message->react($this->whyy);
        $message->react($this->helicopter);
    }

    public function canRun(?Message $message = null): bool
    {
        if (!$message) {
            return false;
        }
        return Str::containsOnePhrase(strtolower($message->content), $this->triggerKeywords);
    }
}
