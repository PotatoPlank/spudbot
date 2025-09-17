<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Commands;

use DI\Attribute\Inject;
use Discord\Parts\Interactions\Interaction;
use Spudbot\Model\Guild;
use Spudbot\Services\GuildService;

class MemberCount extends AbstractCommandSubscriber
{
    #[Inject]
    protected GuildService $guildService;
    public function getCommandName(): string
    {
        return 'counter';
    }

    public function getCommandDescription(): string
    {
        return 'Update the member counter.';
    }

    public function update(?Interaction $interaction = null): void
    {
        if (!$interaction) {
            return;
        }

        if (!$interaction->member->permissions->manage_guild) {
            $this->spud->interact()
                ->error('You don\'t have the necessary permissions to run this command.')
                ->respondTo($interaction);
            return;
        }

        $this->updateChannel($interaction);
    }

    private function updateChannel(Interaction $interaction): void
    {
        $guild = $this->guildService->findOrCreateWithPart($interaction->guild);

        if(!$guild->memberCountChannelId){
            $guild->memberCountChannelId = Guild::locateMemberCountChannel($interaction->guild)?->id;
            $this->guildService->save($guild);
        }

        try{
            $guild->setChannelMemberCount($interaction->guild);
        }catch (\Exception $e){
            $this->spud->interact()
                ->setTitle('Exception Encountered')
                ->setDescription($e->getMessage())
                ->respondTo($interaction);
            return;
        }

        $this->spud->interact()
            ->setTitle('Member Counter')
            ->setDescription("The member counter was updated to: {$interaction->guild->member_count}")
            ->respondTo($interaction);
    }
}
