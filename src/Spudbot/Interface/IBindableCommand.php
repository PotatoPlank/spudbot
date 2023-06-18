<?php
declare(strict_types=1);

namespace Spudbot\Interface;

use Discord\Builders\CommandBuilder;
use Discord\Parts\Interactions\Command\Command;
use Spudbot\Bindable\CommandObserver;
use Spudbot\Bot\Spud;

abstract class IBindableCommand extends IBindable
{

    protected string $name;
    protected string $description;

    protected CommandObserver $observer;

    public function __construct(){

    }
    abstract public function getListener(): callable;

    public function getCommand(): Command
    {
        if(empty($this->getName()) || empty($this->getDescription())){
            throw new \RuntimeException('A name and description must be provided for a command to be bound.');
        }
        $command = CommandBuilder::new();
        $command->setName($this->getName());
        $command->setDescription($this->getDescription());

        return new Command($this->discord, $command->toArray());
    }

    public function getName(): string
    {
        return $this->name;
    }
    public function getDescription(): string
    {
        return $this->description;
    }

    public function setSpudClient(Spud $spud): void
    {
        parent::setSpudClient($spud);
        $this->observer = new CommandObserver($this->spud);
    }
}