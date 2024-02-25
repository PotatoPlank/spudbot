<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Interface;

use Discord\Repository\Interaction\OptionRepository;

abstract class AbstractSubCommandSubscriber extends AbstractSubscriber
{
    protected OptionRepository $options;

    public function hook(): void
    {
        $this->spud->commandObserver->subscribe($this->getCommandName(), $this);
    }

    abstract public function getCommandName(): string;

    public function setOptionRepository(OptionRepository $options): void
    {
        $this->options = $options;
    }
}
