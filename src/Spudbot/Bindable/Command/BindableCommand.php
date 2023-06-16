<?php

namespace Spudbot\Bindable\Command;

use Discord\Parts\Interactions\Command\Command as CommandPart;
use Spudbot\Bindable\Bindable;
use Spudbot\Bindable\CommandObserver;
use Spudbot\Bot\Spud;

abstract class BindableCommand extends Bindable
{

    protected string $name;
    protected string $description;

    protected CommandObserver $observer;

    public function __construct(){

    }
    abstract public function getListener(): callable;

    abstract public function getCommand(): CommandPart;

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