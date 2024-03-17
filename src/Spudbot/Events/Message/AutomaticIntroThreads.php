<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023-2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Events\Message;


use Carbon\Carbon;
use Discord\Parts\Channel\Message;
use Discord\WebSockets\Event;
use Spudbot\Events\AbstractEventSubscriber;
use Spudbot\Model\Member;

class AutomaticIntroThreads extends AbstractEventSubscriber
{
    private const INTRO_CHANNEL_ID = '1114365925366440038';
    private const MOKKA_REACT = ':mokka:1115005842102681770';
    private const DOGE_VIBE_REACT = ':dogevibe:1115010156728680478';

    public function getEventName(): string
    {
        return Event::MESSAGE_CREATE;
    }

    public function update(?Message $message = null): void
    {
        if (!$message || $message->channel_id !== self::INTRO_CHANNEL_ID) {
            return;
        }
        $username = Member::getUsernameWithPart($message->member);
        $tenure = $message->member->joined_at?->diffInDays(Carbon::now()) ?? -99;
        if ($tenure < 0 || $tenure > 30) {
            return;
        }

        $message->react(self::DOGE_VIBE_REACT);
        $message->react(self::MOKKA_REACT);
        $message->startThread($this->spud->twig->render('user/intro_title.twig', [
            'memberName' => $username,
        ]));
    }
}
