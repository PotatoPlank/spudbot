<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

declare(strict_types=1);

namespace Spudbot\Interface;

abstract class IBindableEvent extends IBindable
{
    protected string $event;

    public function getBoundEvent(): string
    {
        return $this->event;
    }

    abstract public function getListener(): callable;
}