<?php

namespace Spudbot\Model;

use Spudbot\Model;

class EventAttendance extends Model
{
    private Event $event;
    private Member $member;
    private string $status;
    private bool $wasNoShow;

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