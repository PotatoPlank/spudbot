<?php

namespace Spudbot\Bot;

use Carbon\Carbon;
use Discord\Discord;
use Doctrine\DBAL\Connection;
use Spudbot\Bindable\Command\BindableCommand;
use Spudbot\Bindable\Event\BindableEvent;
use Spudbot\Bindable\Event\OnReadyExecuteBinds;
use Spudbot\Builder\EmbeddedResponse;
use Spudbot\Collection;

class Spud
{
    private Discord $discord;
    private Collection $events;
    private Collection $commands;
    private ?Connection $dbal;

    public function __construct(SpudOptions $options)
    {
        $this->discord = new Discord($options->getOptions());
        $this->events = new Collection();
        $this->commands = new Collection();
    }

    public function setDoctrineClient(?Connection $dbal): void
    {
        $this->dbal = $dbal;
    }

    public function loadBindableCommand(BindableCommand $command): void
    {
        $command->setDiscordClient($this->discord);
        $command->setSpudClient($this);
        if(!empty($this->dbal)){
            $command->setDoctrineClient($this->dbal);
        }

        $this->commands->push($command);
    }

    public function loadBindableEvent(BindableEvent $event): void
    {
        $event->setDiscordClient($this->discord);
        $event->setSpudClient($this);
        if(!empty($this->dbal)){
            $event->setDoctrineClient($this->dbal);
        }

        if(!isset($this->events[$event->getBoundEvent()])){
            $this->events->set($event->getBoundEvent(), new Collection());
        }
        $this->events->get($event->getBoundEvent())
            ->push($event);
    }

    public function getSimpleResponseBuilder(): EmbeddedResponse
    {
        return new EmbeddedResponse($this->discord);
    }

    public function on(string $event, callable $listener): Discord
    {
        return $this->discord->on($event, $listener);
    }

    public function run(): void
    {
        $onReadyEvent = new OnReadyExecuteBinds();
        $onReadyEvent->setDiscordClient($this->discord);
        $onReadyEvent->setSpudClient($this);
        $onReadyEvent->setEventCollection($this->events);
        $onReadyEvent->setCommandCollection($this->commands);

        $this->discord->on($onReadyEvent->getBoundEvent(), $onReadyEvent->getListener())
            ->run();
    }
}