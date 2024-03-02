<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023-2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Events\Message;


use DI\Attribute\Inject;
use Discord\Parts\Channel\Message;
use Discord\WebSockets\Event;
use Spudbot\Interface\AbstractEventSubscriber;
use Spudbot\Model\Member;
use Spudbot\Services\MemberService;

class CountMemberComments extends AbstractEventSubscriber
{
    #[Inject]
    protected MemberService $memberService;

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
        if ($isBot) {
            $botMember = $this->memberService->findWithPart($message->member);
            if ($botMember) {
                $this->memberService->remove($botMember);
            }
            return;
        }
        $username = Member::getUsernameWithPart($message->member);

        $member = $this->memberService->findOrCreateWithPart($message->member);
        $member->setTotalComments($member->getTotalComments() + 1);
        $member->setUsername($username);

        $this->memberService->save($member);
    }
}
