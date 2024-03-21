<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Services;

use OutOfBoundsException;
use Spudbot\Exception\ApiException;
use Spudbot\Exception\ApiRequestFailure;
use Spudbot\Model\Event;
use Spudbot\Model\EventAttendance;
use Spudbot\Model\Member;
use Spudbot\Repositories\EventAttendanceRepository;
use Spudbot\Repositories\EventRepository;
use Spudbot\Repositories\MemberRepository;

class EventAttendanceService
{
    public function __construct(
        public EventRepository $eventRepository,
        public EventAttendanceRepository $attendanceRepository,
        public GuildService $guildService,
        public MemberRepository $memberRepository
    ) {
    }

    /**
     * @throws ApiRequestFailure
     * @throws ApiException
     */
    public function findOrCreateByMemberAndEvent(Member $member, Event $event): EventAttendance
    {
        try {
            return $this->attendanceRepository->getMembersEventAttendance($member, $event);
        } catch (OutOfBoundsException $exception) {
            return $this->attendanceRepository->save(EventAttendance::create([
                'status' => 'Attendees',
                'event' => $event,
                'member' => $member,
            ]));
        }
    }

    public function save(EventAttendance $attendance): EventAttendance
    {
        return $this->attendanceRepository->save($attendance);
    }
}
