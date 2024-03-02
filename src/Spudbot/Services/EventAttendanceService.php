<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Services;

use OutOfBoundsException;
use Spudbot\Model\Event;
use Spudbot\Model\EventAttendance;
use Spudbot\Model\Member;
use Spudbot\Repository\Api\EventRepository;
use Spudbot\Repository\Api\MemberRepository;

class EventAttendanceService
{
    public function __construct(
        public EventRepository $eventRepository,
        public GuildService $guildService,
        public MemberRepository $memberRepository
    ) {
    }

    public function findOrCreateByMemberAndEvent(Member $member, Event $event): EventAttendance
    {
        try {
            return $this->eventRepository->getAttendanceByMemberAndEvent($member, $event);
        } catch (OutOfBoundsException $exception) {
            return $this->memberRepository->saveMemberEventAttendance(EventAttendance::create([
                'status' => 'Attendees',
                'event' => $event,
                'member' => $member,
            ]));
        }
    }
}
