<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023-2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Events\Meme;


use Carbon\Carbon;
use Discord\Parts\Channel\Message;
use Discord\WebSockets\Event;
use Spudbot\Interface\AbstractEventSubscriber;
use Spudbot\Util\Str;

class GoodMorning extends AbstractEventSubscriber
{
    private string $reaction = ':sun_with_face:';
    private string $guildTimezone = 'America/New_York';
    private array $triggerPhrases = [
        'good morning',
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
        $hasGreeted = Str::containsOnePhrase(strtolower($message->content), $this->triggerPhrases);
        $morningStart = Carbon::now($this->guildTimezone)->setTime(3, 0);
        $morningEnd = Carbon::now($this->guildTimezone)->setTime(12, 0);
        $isMorning = $message->timestamp->setTimezone($this->guildTimezone)->between($morningStart, $morningEnd);
        if ($hasGreeted && $isMorning) {
            $message->react($this->reaction);
        }
    }
}
