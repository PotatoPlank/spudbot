<?php

namespace Spudbot\Bindable\Command;

use Discord\Parts\Interactions\Command\Command as CommandPart;
use Spudbot\Bindable\Bindable;

abstract class BindableCommand extends Bindable
{

    abstract public function getListener(): callable;

    abstract public function getCommand(): CommandPart;

    abstract public function getName(): string;
    abstract public function getDescription(): string;
}