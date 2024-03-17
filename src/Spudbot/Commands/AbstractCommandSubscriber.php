<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Commands;

use BadMethodCallException;
use Discord\Builders\CommandBuilder;
use Discord\Parts\Interactions\Command\Command;
use Spudbot\Bot\AbstractSubscriber;
use Spudbot\Bot\Spud;
use Spudbot\Bot\SubCommandObserver;

abstract class AbstractCommandSubscriber extends AbstractSubscriber
{
    protected SubCommandObserver $subCommandObserver;

    public function __construct(Spud $spud)
    {
        parent::__construct($spud);
        $this->subCommandObserver = new SubCommandObserver($spud);
    }

    public function hook(): void
    {
        $this->spud->commandObserver->subscribe($this->getCommandName(), $this);
    }

    abstract public function getCommandName(): string;

    public function getCommand(): Command
    {
        if (empty($this->getCommandName()) || empty($this->getCommandDescription())) {
            throw new BadMethodCallException('Encountered an empty command name or description.');
        }
        $command = CommandBuilder::new();
        $command->setName($this->getCommandName());
        $command->setDescription($this->getCommandDescription());

        return new Command($this->spud->discord, $command->toArray());
    }

    abstract public function getCommandDescription(): string;

    public function throwMissingArgs(array $missingArgs = []): void
    {
        $list = !empty($missingArgs) ? implode(', ', $missingArgs) : '';
        $message = !empty($missingArgs) ? "Missing arguments {$list} for command " : 'Missing arguments for command ';
        throw new BadMethodCallException($message . static::class);
    }
}
