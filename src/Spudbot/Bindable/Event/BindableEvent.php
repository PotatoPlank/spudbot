<?php

namespace Spudbot\Bindable\Event;

use Spudbot\Bindable\Bindable;

abstract class BindableEvent extends Bindable
{
    abstract public function getBoundEvent(): string;

    abstract public function getListener(): callable;
}