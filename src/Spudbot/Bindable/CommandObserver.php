<?php

namespace Spudbot\Bindable;

use Discord\Repository\Interaction\OptionRepository;
use Spudbot\Bot\Spud;
use Spudbot\Helpers\Collection;
use Spudbot\Interface\ISubCommand;

class CommandObserver
{
    private Collection $subscribers;
    private \Closure $defaultListener;

    public function __construct(protected Spud $spud){
        $this->subscribers = new Collection();
    }

    public function subscribe(ISubCommand $command): void
    {
        $this->subscribers->set($command->getSubCommand(), $command);
    }

    public function notify(OptionRepository $options, mixed ...$arguments): void
    {
        $notified = false;
        /**
         * @var ISubCommand $subscriber
         */
        foreach ($this->subscribers as $subCommand => $subscriber)
        {
            if($options->isset($subCommand)){
                $subscriber->setOptionRepository($options[$subCommand]->options);
                $subscriber->setSpudClient($this->spud);
                $subscriber->execute(...$arguments);
                $notified = true;
            }
        }
        if(!$notified && isset($this->defaultListener))
        {
            $defaultListener = $this->defaultListener;
            $defaultListener(...$arguments);
        }
    }

    public function setDefaultListener(callable $listener): void
    {
        $this->defaultListener = $listener(...);
    }
}