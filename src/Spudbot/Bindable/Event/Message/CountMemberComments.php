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
use Spudbot\Model\Member;

class CountMemberComments extends IBindableEvent
{

    public function getBoundEvent(): string
    {
        return Event::MESSAGE_CREATE;
    }

    public function getListener(): callable
    {
        return function (Message $message) {
            if ($message->member && !$message->member->user->bot) {
                $username = $message->member->nick ?? $message->member->displayname;
                $memberRepository = $this->spud->getMemberRepository();

                try {
                    $member = $memberRepository->findByPart($message->member);
                    $member->setTotalComments($member->getTotalComments() + 1);
                    $member->setUsername($username);
                } catch (\OutOfBoundsException) {
                    $member = new Member();
                    $member->setGuild($this->spud->getGuildRepository()->findByPart($message->guild));
                    $member->setDiscordId($message->member->id);
                    $member->setTotalComments(1);
                    $member->setUsername($username);
                }
                $memberRepository->save($member);
            } elseif ($message->member->user->bot) {
                $memberRepository = $this->spud->getMemberRepository();
                try {
                    $member = $memberRepository->findByPart($message->member);

                    $memberRepository->remove($member);
                } catch (\OutOfBoundsException) {
                    /**
                     * Don't add the bot
                     */
                }
            }
        };
    }
}