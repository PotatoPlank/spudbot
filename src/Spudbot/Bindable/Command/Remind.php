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
use Spudbot\Interface\IBindableCommand;

class Remind extends IBindableCommand
{
    protected string $name = 'remind';
    protected string $description = 'Creates a reminder at the specified datetime.';

    public function getListener(): callable
    {
        return function (Interaction $interaction) {
        };
    }

    public function getCommand(): Command
    {
        $user = new Option($this->discord);
        $user->setName('message')
            ->setDescription('The message of the reminder.')
            ->setRequired(true)
            ->setType(Option::STRING);

        $reason = new Option($this->discord);
        $reason->setName('datetime')
            ->setDescription('When the reminder should be sent (US eastern timezone).')
            ->setRequired(true)
            ->setType(Option::STRING);

        $reason = new Option($this->discord);
        $reason->setName('channel')
            ->setDescription('The location of where the reminder should be sent.')
            ->setRequired(true)
            ->setType(Option::CHANNEL);

        $command = CommandBuilder::new();
        $command->setName($this->getName())
            ->setDescription($this->getDescription())
            ->addOption($user)->addOption($reason);

        return new Command($this->discord, $command->toArray());
    }

    public function checkRequirements(): void
    {
        if (empty($this->dbal)) {
            throw new \RuntimeException(
                "Command '{$this->getName()}' requires a DBAL Client to function appropriately."
            );
        }
    }
}