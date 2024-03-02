<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Model;

use Carbon\Carbon;
use Spudbot\Interface\AbstractModel;

class EventAttendance extends AbstractModel
{
    private Event $event;
    private Member $member;
    private string $status;
    private bool $wasNoShow = false;

    public static function hydrateWithArray(array $row): self
    {
        $eventAttendance = new self();

        $eventAttendance->setId($row['external_id']);
        $eventAttendance->setStatus($row['status']);
        $eventAttendance->wasNoShow((bool)$row['no_show']);
        $eventAttendance->setCreatedAt(Carbon::parse($row['created_at']));
        $eventAttendance->setModifiedAt(Carbon::parse($row['updated_at']));


        if (isset($row['event'])) {
            $eventAttendance->setEvent(Event::hydrateWithArray($row['event']));
        }
        if (isset($row['member'])) {
            $eventAttendance->setMember(Member::hydrateWithArray($row['member']));
        }

        return $eventAttendance;
    }

    public function wasNoShow(bool $status): void
    {
        $this->wasNoShow = $status;
    }

    public function getEvent(): Event
    {
        return $this->event;
    }

    public function setEvent(Event $event): void
    {
        $this->event = $event;
    }

    public function getMember(): Member
    {
        return $this->member;
    }

    public function setMember(Member $member): void
    {
        $this->member = $member;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getNoShowStatus(): bool
    {
        return $this->wasNoShow;
    }
}
