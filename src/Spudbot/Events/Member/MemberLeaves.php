<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Events\Member;


use Discord\Parts\User\Member;
use Discord\WebSockets\Event;
use Spudbot\Interface\AbstractEventSubscriber;
use Spudbot\Model\Guild;

class MemberLeaves extends AbstractEventSubscriber
{
    public function getEventName(): string
    {
        return Event::GUILD_MEMBER_REMOVE;
    }

    public function update(?Member $member = null): void
    {
        if (!$member) {
            return;
        }
        Guild::updateMemberCount($member->guild, $this->spud->discord);
    }
}
