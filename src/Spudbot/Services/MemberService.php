<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Services;

use Carbon\Carbon;
use OutOfBoundsException;
use Spudbot\Helpers\Collection;
use Spudbot\Model\EventAttendance;
use Spudbot\Model\Member;
use Spudbot\Repositories\EventAttendanceRepository;
use Spudbot\Repositories\MemberRepository;

class MemberService
{
    public function __construct(
        public MemberRepository $memberRepository,
        public GuildService $guildService,
        public EventAttendanceRepository $attendanceRepository
    ) {
    }

    public function findOrCreateWithPart(\Discord\Parts\User\Member $member): ?Member
    {
        try {
            $model = $this->memberRepository->findWithPart($member);
            if ($model) {
                return $model;
            }
            throw new OutOfBoundsException('Does not exist.');
        } catch (OutOfBoundsException $exception) {
            return $this->save(Member::create([
                'discordId' => $member->id,
                'totalComments' => 0,
                'username' => Member::getUsernameWithPart($member),
                'verifiedBy' => null,
                'guild' => $this->guildService->findOrCreateWithPart($member->guild),
            ]));
        }
    }

    public function findWithPart(\Discord\Parts\User\Member $member): ?Member
    {
        try {
            $model = $this->memberRepository->findWithPart($member);
            if ($model) {
                return $model;
            }
            throw new OutOfBoundsException('Does not exist.');
        } catch (OutOfBoundsException $exception) {
            return null;
        }
    }

    public function save(Member $member): Member
    {
        return $this->memberRepository->save($member);
    }

    public function getAttendanceStatistics(Member $member): array
    {
        $attendances = $this->getEventAttendance($member);
        $totalInterested = count($attendances);
        $totalAttended = 0;
        $reputation = 0;
        if ($totalInterested > 0) {
            /**
             * @var EventAttendance $event
             */
            foreach ($attendances as $event) {
                if ($event->getEvent()->getScheduledAt()->gt(Carbon::now())) {
                    $totalInterested--;
                } elseif (!$event->getNoShow()) {
                    $totalAttended++;
                }
            }

            $reputation = round(($totalAttended / $totalInterested) * 100);
        }

        return [
            'interested' => $totalInterested,
            'attended' => $totalAttended,
            'reputation' => $reputation,
        ];
    }

    public function getEventAttendance(Member $member): Collection
    {
        return $this->attendanceRepository->getMemberAttendance($member);
    }

    public function remove(Member $member): bool
    {
        return $this->memberRepository->remove($member);
    }
}
