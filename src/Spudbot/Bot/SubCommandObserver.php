<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Bot;

use Discord\Repository\Interaction\OptionRepository;
use Spudbot\Helpers\Collection;
use Spudbot\Interface\AbstractSubCommandSubscriber;

class SubCommandObserver
{
    private Collection $subscribers;
    private \Closure $defaultListener;

    public function __construct(protected Spud $spud)
    {
        $this->subscribers = new Collection();
    }

    public function subscribe(string $command): void
    {
        $subscriber = new $command($this->spud);
        $this->subscribers->set($subscriber->getCommandName(), $command);
    }

    public function notify(OptionRepository $options, mixed ...$arguments): void
    {
        $notified = false;
        /**
         * @var AbstractSubCommandSubscriber $subscriber
         */
        foreach ($this->subscribers as $subCommand => $subscriber) {
            if ($options->isset($subCommand)) {
                $subscriber->setOptionRepository($options[$subCommand]->options);
                $subscriber->hook(...$arguments);
                $notified = true;
            }
        }
        if (!$notified && isset($this->defaultListener)) {
            $defaultListener = $this->defaultListener;
            $defaultListener(...$arguments);
        }
    }

    public function setDefaultListener(callable $listener): void
    {
        $this->defaultListener = $listener(...);
    }
}
