<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023-2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Events\Message;


use Carbon\Carbon;
use DI\Attribute\Inject;
use Discord\Parts\Channel\Message;
use Discord\WebSockets\Event;
use Spudbot\Events\AbstractEventSubscriber;
use Spudbot\Model\Member;
use Spudbot\Services\GuildService;

class AutomaticIntroThreads extends AbstractEventSubscriber
{
    private const MOKKA_REACT = ':mokka:1115005842102681770';
    private const DOGE_VIBE_REACT = ':dogevibe:1115010156728680478';

    #[Inject]
    private GuildService $guildService;

    public function getEventName(): string
    {
        return Event::MESSAGE_CREATE;
    }

    public function update(?Message $message = null): void
    {
        if (!$message) {
            return;
        }
        $username = Member::getUsernameWithPart($message->member);

        $message->react(self::DOGE_VIBE_REACT);
        $message->react(self::MOKKA_REACT);
        $message->startThread($this->spud->twig->render('user/intro_title.twig', [
            'memberName' => $username,
        ]));
    }

    public function canRun(?Message $message = null): bool
    {
        if (!$message) {
            return false;
        }
        $guildPart = $this->spud->discord->guilds->get('id', $message->guild_id);
        if (!$guildPart) {
            return false;
        }
        $guild = $this->guildService->findOrCreateWithPart($guildPart);
        $hasIntroChannel = $guild->hasIntroductionsChannel();
        $isIntroChannel = $message->channel_id === $guild->channelIntroductionId;
        $isNewMember = ($message->member->joined_at?->diffInDays(Carbon::now()) ?? -99) <= 30;
        return $isNewMember && $hasIntroChannel && $isIntroChannel;
    }
}
