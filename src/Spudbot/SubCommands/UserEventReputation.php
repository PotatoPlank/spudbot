<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

declare(strict_types=1);

namespace Spudbot\SubCommands;


use DI\Attribute\Inject;
use Discord\Parts\Interactions\Interaction;
use Spudbot\Interface\AbstractSubCommandSubscriber;
use Spudbot\Services\MemberService;

class UserEventReputation extends AbstractSubCommandSubscriber
{
    #[Inject]
    protected MemberService $memberService;

    public function getCommandName(): string
    {
        return 'reputation';
    }

    public function update(?Interaction $interaction = null): void
    {
        if (!$interaction) {
            return;
        }
        $builder = $this->spud->interact()
            ->setTitle("Event Attendance");
        $userId = $this->options['user']->value;
        $memberPart = $interaction->guild->members->get('id', $userId);
        if (!$memberPart) {
            $builder->error("Unable to find member $userId")
                ->respondTo($interaction);
            return;
        }

        $member = $this->memberService->findOrCreateWithPart($memberPart);
        $eventStatistics = $this->memberService->getAttendanceStatistics($member);

        if ($eventStatistics['interested'] > 0) {
            $message = $this->spud->twig->render('user/event_reputation.twig', [
                'memberId' => $memberPart->id,
                'reputation' => $eventStatistics['reputation'],
                'eventsAttended' => $eventStatistics['attended'],
                'eventsInterested' => $eventStatistics['interested'],
            ]);

            $builder->setDescription($message);
        } else {
            $builder->setDescription("<@{$memberPart->id}> hasn't attended an event yet.");
        }
        $builder->respondTo($interaction);
    }
}
