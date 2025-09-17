<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Events\Member;


use DI\Attribute\Inject;
use Discord\Parts\User\Member;
use Discord\WebSockets\Event;
use Spudbot\Events\AbstractEventSubscriber;
use Spudbot\Model\Guild;
use Spudbot\Services\GuildService;

class MemberLeaves extends AbstractEventSubscriber
{
    #[Inject]
    protected GuildService $guildService;
    public function getEventName(): string
    {
        return Event::GUILD_MEMBER_REMOVE;
    }

    public function update(?Member $member = null): void
    {
        if (!$member) {
            return;
        }

        $this->updateChannel($member);
    }


    private function updateChannel(Member $member): void
    {
        $guild = $this->guildService->findOrCreateWithPart($member->guild);

        if(!$guild->memberCountChannelId){
            $guild->memberCountChannelId = Guild::locateMemberCountChannel($member->guild)?->id;
            $this->guildService->save($guild);
        }

        try{
            $guild->setChannelMemberCount($member->guild);
        }catch (\Exception $e){
            // TODO: Log $e
            return;
        }
    }
}
