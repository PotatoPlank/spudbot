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

class UserNoShowStatus extends AbstractSubCommandSubscriber
{
    #[Inject]
    protected MemberService $memberService;

    public function update(?Interaction $interaction = null): void
    {
        if (!$interaction) {
            return;
        }
        $userId = $this->options['user']->value;
        $eventId = $this->options['internal_id']->value;
        $noShowStatus = $this->options['status']->value ?? true;
        $memberPart = $interaction->guild->members->get('id', $userId);
        if (!$memberPart) {
            $this->spud->interact()
                ->error("Unable to find member $userId")
                ->respondTo($interaction);
            return;
        }

        $member = $this->memberService->findOrCreateWithPart($memberPart);

        try {
            $event = $this->spud->eventRepository->findById($eventId);
            $eventAttendance = $this->spud->eventRepository->getAttendanceByMemberAndEvent($member, $event);

            $eventAttendance->wasNoShow($noShowStatus);

            $this->spud->memberRepository->saveMemberEventAttendance($eventAttendance);

            $message = "<@{$member->getDiscordId()}>'s status was updated.";
        } catch (\OutOfBoundsException $exception) {
            $message = 'An event with that id and user could not be found.';
        }

        $this->spud->interact()
            ->setTitle('Event No Show')
            ->setDescription($message)
            ->respondTo($interaction);
    }

    public function getCommandName(): string
    {
        return 'no_show';
    }
}