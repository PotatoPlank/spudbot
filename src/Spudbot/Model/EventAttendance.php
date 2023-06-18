<?php

namespace Spudbot\Model;

use Carbon\Carbon;
use Spudbot\Interface\IModel;

class EventAttendance extends IModel
{
    private Event $event;
    private Member $member;
    private string $status;
    private bool $wasNoShow;

    public static function withDatabaseRow(array $row, Event $event, Member $member): self
    {
        $eventAttendance = new self();

        $eventAttendance->setId($row['ea_id'] ?? $row['id']);
        $eventAttendance->setEvent($event);
        $eventAttendance->setMember($member);
        $eventAttendance->setStatus($row['ea_status'] ?? $row['status']);
        $eventAttendance->wasNoShow((bool) $row['ea_no_show'] ?? $row['no_show']);
        $eventAttendance->setCreatedAt(Carbon::parse($row['ea_created_at'] ?? $row['created_at']));
        $eventAttendance->setModifiedAt(Carbon::parse($row['ea_modified_at'] ?? $row['modified_at']));

        return $eventAttendance;
    }

    public function setEvent(Event $event): void
    {
        $this->event = $event;
    }

    public function getEvent(): Event
    {
        return $this->event;
    }

    public function setMember(Member $member): void
    {
        $this->member = $member;
    }

    public function getMember(): Member
    {
        return $this->member;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function wasNoShow(bool $status): void
    {
        $this->wasNoShow = $status;
    }

    public function getNoShowStatus(): bool
    {
        return $this->wasNoShow;
    }
}