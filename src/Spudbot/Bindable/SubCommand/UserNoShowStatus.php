<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

declare(strict_types=1);

namespace Spudbot\Bindable\SubCommand;


use Discord\Parts\Interactions\Interaction;
use Spudbot\Interface\ISubCommand;
use Spudbot\Repository\SQL\EventRepository;
use Spudbot\Repository\SQL\MemberRepository;

class UserNoShowStatus extends ISubCommand
{
    protected string $subCommand = 'no_show';

    public function execute(?Interaction $interaction): void
    {
        /**
         * @var EventRepository $eventRepository
         * @var MemberRepository $memberRepository
         */
        $eventRepository = $this->spud->eventRepository;
        $memberRepository = $this->spud->memberRepository;
        $builder = $this->spud->getSimpleResponseBuilder();
        $title = 'Event No Show';

        $userId = $this->options['user']->value;
        $eventId = $this->options['internal_id']->value;
        $noShowStatus = $this->options['status']->value ?? true;
        $memberPart = $interaction->guild->members->get('id', $userId);

        $member = $memberRepository->findByPart($memberPart);

        try {
            $event = $eventRepository->findById($eventId);
            $eventAttendance = $eventRepository->getAttendanceByMemberAndEvent($member, $event);

            $eventAttendance->wasNoShow($noShowStatus);

            $memberRepository->saveMemberEventAttendance($eventAttendance);

            $message = "<@{$member->getDiscordId()}>'s status was updated.";
        } catch (\OutOfBoundsException $exception) {
            $message = 'An event with that id and user could not be found.';
        }

        $builder->setTitle($title);
        $builder->setDescription($message);
        $interaction->respondWithMessage($builder->getEmbeddedMessage());
    }
}
