<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023-2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Commands;

use Discord\Builders\CommandBuilder;
use Discord\Parts\Interactions\Command\Command;
use Discord\Parts\Interactions\Command\Option;
use Discord\Parts\Interactions\Interaction;
use Discord\Parts\Permissions\Permission;
use Spudbot\Interface\AbstractCommandSubscriber;
use Spudbot\SubCommands\Coven\Give;
use Spudbot\SubCommands\Coven\Remove;

class Coven extends AbstractCommandSubscriber
{
    public function update(?Interaction $interaction = null): void
    {
        if (!$interaction) {
            return;
        }
        $this->subCommandObserver->subscribeAll([
            Give::class,
            Remove::class
        ]);
        $this->subCommandObserver->notify($interaction->data->options, $interaction);
    }

    public function getCommand(): Command
    {
        $give = new Option($this->spud->discord);
        $give->setName('give');
        $give->setDescription('Give access.');
        $give->setType(Option::SUB_COMMAND);

        $remove = new Option($this->spud->discord);
        $remove->setName('remove');
        $remove->setDescription('Remove access.');
        $remove->setType(Option::SUB_COMMAND);

        $user = new Option($this->spud->discord);
        $user->setName('user')
            ->setDescription('The targeted user.')
            ->setRequired(true)
            ->setType(Option::USER);

        $give->addOption($user);
        $remove->addOption($user);

        $command = CommandBuilder::new();
        $command->setName($this->getCommandName());
        $command->setDescription($this->getCommandDescription());
        $command->addOption($give);
        $command->addOption($remove);
        $command->setDefaultMemberPermissions(Permission::ROLE_PERMISSIONS['manage_guild']);

        return new Command($this->spud->discord, $command->toArray());
    }

    public function getCommandName(): string
    {
        return 'coven';
    }

    public function getCommandDescription(): string
    {
        return 'Manage member access to the private coven chat.';
    }
}
