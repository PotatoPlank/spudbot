<?php
declare(strict_types=1);

namespace Spudbot\Interface;

abstract class IBindableEvent extends IBindable
{
    abstract public function getBoundEvent(): string;

    abstract public function getListener(): callable;
}