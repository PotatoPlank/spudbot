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
use Spudbot\Events\AbstractEventSubscriber;
use Spudbot\Util\Str;

class GoodAfternoon extends AbstractEventSubscriber
{
    private string $reaction = '☀';
    private string $guildTimezone = 'America/New_York';
    private string $start = '12:00';
    private string $end = '17:00';
    private array $triggerPhrases = [
        'good afternoon',
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

        $message->react($this->reaction);
    }

    public function canRun(?Message $message = null): bool
    {
        if (!$message) {
            return false;
        }
        $hasGreeted = Str::containsOnePhrase(strtolower($message->content), $this->triggerPhrases);
        $messageSentAt = $message->timestamp->clone()->setTimezone($this->guildTimezone);
        $afternoonStart = Carbon::now($this->guildTimezone)->setTimeFromTimeString($this->start);
        $afternoonEnd = Carbon::now($this->guildTimezone)->setTimeFromTimeString($this->end);
        $isAfternoon = $messageSentAt->isBetween($afternoonStart, $afternoonEnd);

        return $hasGreeted && $isAfternoon;
    }
}
