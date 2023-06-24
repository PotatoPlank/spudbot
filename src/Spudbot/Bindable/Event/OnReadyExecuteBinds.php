<?php

namespace Spudbot\Bindable\Event;

use Carbon\Carbon;
use Spudbot\Bot\Spud;
use Spudbot\Helpers\Collection;
use Spudbot\Interface\IBindableEvent;

class OnReadyExecuteBinds extends IBindableEvent
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
                    $commandClass = get_class($command);
                    $this->discord->application->commands->save($command->getCommand());
                    $this->discord->listenCommand($command->getName(), function(...$args) use ($command, $commandClass){
                        ($command->getListener())(...$args);

                        $this->discord->getLogger()
                            ->info("Slash command '{$command->getName()}' called using '{$commandClass}'.");
                    });
                    $this->discord->getLogger()
                        ->info("Slash command '{$command->getName()}' bound using '{$commandClass}'.");
                }
            }
            if(!empty($this->events))
            {
                foreach ($this->events as $eventType => $eventCollection) {
                    foreach ($eventCollection as $event) {
                        $eventClass = get_class($event);
                        $this->discord->on($event->getBoundEvent(), function(...$args) use ($event, $eventClass){
                            ($event->getListener())(...$args);

                            $this->discord->getLogger()
                                ->info("Event '{$event->getBoundEvent()}' called using '{$eventClass}'.");
                        });
                        $this->discord->getLogger()
                            ->info("Event listening to '{$event->getBoundEvent()}' using '{$eventClass}'.");
                    }
                }
            }
            if(!empty($this->spud->logGuild) && $_ENV['SENTRY_ENV'] !== 'dev'){
                $output = $this->discord->guilds->get('id', $this->spud->logGuild->getDiscordId())->channels->get('id', $this->spud->logGuild->getOutputChannelId());
                if(!empty($this->spud->logGuild->getOutputThreadId())){
                    $output = $output->threads->get('id', $this->spud->logGuild->getOutputThreadId());
                }
                $builder = $this->spud->getSimpleResponseBuilder();
                $builder->setTitle('Bot started');
                $builder->setDescription("Spudbot started. " . Spud::getVersionString());
                $output->sendMessage($builder->getEmbeddedMessage());
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