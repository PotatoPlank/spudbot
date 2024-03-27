<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\SubCommands;

use Discord\Helpers\Collection;
use Spudbot\Bot\AbstractSubscriber;

abstract class AbstractSubCommandSubscriber extends AbstractSubscriber
{
    protected Collection $options;

    public function hook(): void
    {
        $this->spud->commandObserver->subscribe($this->getCommandName(), $this);
    }

    abstract public function getCommandName(): string;

    public function setOptionRepository(Collection $options): void
    {
        $this->options = $options;
    }
}
