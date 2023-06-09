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

    public static function withDatabaseRow(array $row, ?Event $event = null, ?Member $member = null): self
    {
        $eventAttendance = new self();

        if(array_key_exists('ea_id', $row)){
            $eventAttendance->setId($row['ea_id']);
            $eventAttendance->setEvent(Event::withDatabaseRow($row));
            $eventAttendance->setMember(Member::withDatabaseRow($row));
            $eventAttendance->setStatus($row['ea_status']);
            $eventAttendance->wasNoShow((bool) $row['ea_no_show']);
            $eventAttendance->setCreatedAt(Carbon::parse($row['ea_created_at']));
            $eventAttendance->setModifiedAt(Carbon::parse($row['ea_modified_at']));
        }else{
            if(!isset($event, $member)){
                throw new \InvalidArgumentException('Member and event is required when you\'re not using joins.');
            }
            $eventAttendance->setId($row['id']);
            $eventAttendance->setEvent($event);
            $eventAttendance->setMember($member);
            $eventAttendance->setStatus($row['status']);
            $eventAttendance->wasNoShow((bool) $row['no_show']);
            $eventAttendance->setCreatedAt(Carbon::parse($row['created_at']));
            $eventAttendance->setModifiedAt(Carbon::parse($row['modified_at']));
        }

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