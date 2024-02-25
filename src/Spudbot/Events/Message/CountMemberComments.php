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
use Spudbot\Model\Member;

class CountMemberComments extends AbstractEventSubscriber
{

    public function getEventName(): string
    {
        return Event::MESSAGE_CREATE;
    }

    public function update(?Message $message = null): void
    {
        if (!$message || !$message->member) {
            return;
        }

        $isBot = isset($message->member->user->bot) && $message->member->user->bot;
        if (!$isBot) {
            $username = $message->member->nick ?? $message->member->displayname;
            $memberRepository = $this->spud->memberRepository;

            try {
                $member = $memberRepository->findByPart($message->member);
                $member->setTotalComments($member->getTotalComments() + 1);
                $member->setUsername($username);
            } catch (\OutOfBoundsException) {
                $member = new Member();
                $member->setGuild($this->spud->guildRepository->findByPart($message->guild));
                $member->setDiscordId($message->member->id);
                $member->setTotalComments(1);
                $member->setUsername($username);
            }
            $memberRepository->save($member);
            return;
        }

        $memberRepository = $this->spud->memberRepository;
        try {
            $member = $memberRepository->findByPart($message->member);

            $memberRepository->remove($member);
        } catch (\OutOfBoundsException) {
            /**
             * Don't add the bot
             */
        }
    }
}
