<?php

namespace Spudbot\Bindable\Event;

use Spudbot\Collection;

class OnReadyExecuteBinds extends BindableEvent
{
    private Collection $commands;
    private Collection $events;

    public function getBoundEvent(): string
    {
        return 'ready';
    }

    public function getListener(): callable
    {
        return function () {
            if(!empty($this->commands))
            {
                foreach ($this->commands as $command) {
                    $this->discord->application->commands->save($command->getCommand());
                    $this->discord->listenCommand($command->getName(),$command->getListener());
                    $this->discord->getLogger()
                        ->info("Slash command '{$command->getName()}' bound.");
                }
            }
            if(!empty($this->events))
            {
                foreach ($this->events as $eventType => $eventCollection) {
                    foreach ($eventCollection as $event) {
                        $this->discord->on($event->getBoundEvent(), $event->getListener());
                        $this->discord->getLogger()
                            ->info("Event listening to '{$event->getBoundEvent()}'.");
                    }
                }
            }
        };
    }

    public function setCommandCollection(Collection $commands): void
    {
        $this->commands = $commands;
    }

    public function setEventCollection(Collection $events): void
    {
        $this->events = $events;
    }
}