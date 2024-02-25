<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

declare(strict_types=1);

namespace Spudbot\SubCommands;


use Carbon\Carbon;
use Discord\Parts\Interactions\Interaction;
use Spudbot\Interface\AbstractSubCommandSubscriber;
use Spudbot\Model\EventAttendance;
use Spudbot\Repository\SQL\MemberRepository;

class UserEventReputation extends AbstractSubCommandSubscriber
{
    public function getCommandName(): string
    {
        return 'reputation';
    }

    public function update(?Interaction $interaction = null): void
    {
        /**
         * @var MemberRepository $repository
         */
        $repository = $this->spud->memberRepository;
        $builder = $this->spud->getSimpleResponseBuilder();
        $builder->setTitle("Event Attendance");
        $userId = $this->options['user']->value;
        $memberPart = $interaction->guild->members->get('id', $userId);

        $member = $repository->findByPart($memberPart);
        $eventsAttended = $repository->getEventAttendance($member);
        $totalEvents = count($eventsAttended);
        $totalAttended = 0;
        if ($totalEvents > 0) {
            /**
             * @var EventAttendance $event
             */
            foreach ($eventsAttended as $event) {
                if ($event->getEvent()->getScheduledAt()->gt(Carbon::now())) {
                    $totalEvents--;
                } else {
                    if (!$event->getNoShowStatus()) {
                        $totalAttended++;
                    }
                }
            }

            $reputation = round(($totalAttended / $totalEvents) * 100);
            $context = [
                'memberId' => $memberPart->id,
                'reputation' => $reputation,
                'eventsAttended' => $totalAttended,
                'eventsInterested' => $totalEvents,
            ];
            $message = $this->spud->twig->render('user/event_reputation.twig', $context);

            $builder->setDescription($message);
        } else {
            $builder->setDescription("<@{$memberPart->id}> hasn't attended an event yet.");
        }
        $interaction->respondWithMessage($builder->getEmbeddedMessage());
    }
}
