<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Bindable\Command;

use Discord\Builders\CommandBuilder;
use Discord\Parts\Interactions\Command\Command;
use Discord\Parts\Interactions\Command\Option;
use Discord\Parts\Interactions\Interaction;
use Discord\Parts\Permissions\Permission;
use Spudbot\Bindable\SubCommand\Coven\Give;
use Spudbot\Bindable\SubCommand\Coven\Remove;
use Spudbot\Interface\IBindableCommand;

class Coven extends IBindableCommand
{
    protected string $name = 'coven';
    protected string $description = 'Manage member access to the private coven chat.';

    public function getListener(): callable
    {
        return function (Interaction $interaction) {
            $this->observer->subscribe(new Give());
            $this->observer->subscribe(new Remove());
            $this->observer->notify($interaction->data->options, $interaction);
        };
    }

    public function getCommand(): Command
    {
        $give = new Option($this->discord);
        $give->setName('give');
        $give->setDescription('Give access.');
        $give->setType(Option::SUB_COMMAND);

        $remove = new Option($this->discord);
        $remove->setName('remove');
        $remove->setDescription('Remove access.');
        $remove->setType(Option::SUB_COMMAND);

        $user = new Option($this->discord);
        $user->setName('user')
            ->setDescription('The targeted user.')
            ->setRequired(true)
            ->setType(Option::USER);

        $give->addOption($user);
        $remove->addOption($user);

        $command = CommandBuilder::new();
        $command->setName($this->getName());
        $command->setDescription($this->getDescription());
        $command->addOption($give);
        $command->addOption($remove);
        $command->setDefaultMemberPermissions(Permission::ROLE_PERMISSIONS['manage_guild']);

        return new Command($this->discord, $command->toArray());
    }
}