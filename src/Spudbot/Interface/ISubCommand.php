<?php

namespace Spudbot\Bindable\Sub;


use Discord\Parts\Interactions\Interaction;
use Discord\Repository\Interaction\OptionRepository;
use Spudbot\Interface\IBindable;

abstract class ISubCommand extends IBindable
{
    protected string $subCommand;
    protected OptionRepository $options;

    abstract public function execute(?Interaction $interaction): void;

    public function getSubCommand(): string
    {
        return $this->subCommand;
    }

    public function setOptionRepository(OptionRepository $options)
    {
        $this->options = $options;
    }
}