<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Model;

use Spudbot\Hydrator\Strategy\ModelStrategy;
use Spudbot\Hydrator\Strategy\UsesStrategy;

class EventAttendance extends AbstractModel
{
    #[UsesStrategy(new ModelStrategy(Event::class))]
    private Event $event;
    #[UsesStrategy(new ModelStrategy(Member::class))]
    private Member $member;
    private string $status;
    private bool $noShow = false;

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

    public function getNoShow(): bool
    {
        return $this->noShow;
    }

    public function setNoShow(bool $status): void
    {
        $this->noShow = $status;
    }
}
