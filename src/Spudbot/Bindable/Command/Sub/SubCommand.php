<?php

namespace Spudbot\Bindable\Command\Sub;


use Discord\Parts\Interactions\Interaction;
use Discord\Repository\Interaction\OptionRepository;
use Spudbot\Bindable\Bindable;

abstract class SubCommand extends Bindable
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