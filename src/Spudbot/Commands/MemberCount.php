<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Commands;

use Discord\Parts\Interactions\Interaction;
use Spudbot\Interface\AbstractCommandSubscriber;
use Spudbot\Model\Guild;

class MemberCount extends AbstractCommandSubscriber
{
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

        $builder = $this->spud->getSimpleResponseBuilder();
        if (!$interaction->member->permissions->manage_guild) {
            $builder->setTitle('Invalid Permissions for Counter');
            $builder->setDescription('You don\'t have the necessary permissions to run this command.');

            $interaction->respondWithMessage($builder->getEmbeddedMessage());
            return;
        }

        Guild::updateMemberCount($interaction->guild, $this->spud->discord);


        $builder->setTitle('Member Counter');
        $builder->setDescription("The member counter was updated to: {$interaction->guild->member_count}");

        $interaction->respondWithMessage($builder->getEmbeddedMessage());
    }
}
