<?php
declare(strict_types=1);

namespace Spudbot\Interface;


use Discord\Parts\Interactions\Interaction;
use Discord\Repository\Interaction\OptionRepository;

abstract class ISubCommand extends IBindable
{
    protected string $subCommand;
    protected OptionRepository $options;

    abstract public function execute(?Interaction $interaction): void;

    public function getSubCommand(): string
    {
        return $this->subCommand;
    }

    public function setOptionRepository(OptionRepository $options): void
    {
        $this->options = $options;
    }
}