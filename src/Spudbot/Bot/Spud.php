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
use Spudbot\Repository;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class Spud
{
    private Discord $discord;
    private Collection $events;
    private Collection $commands;
    private ?Connection $dbal;
    private Repository $memberRepository;
    private Repository $eventRepository;
    private Repository $guildRepository;
    private Repository $threadRepository;
    private Environment $twig;


    public function __construct(SpudOptions $options)
    {
        $this->discord = new Discord($options->getOptions());
        $this->events = new Collection();
        $this->commands = new Collection();
        $loader = new FilesystemLoader(dirname(__DIR__, 2) . '/views');
        $this->twig = new Environment($loader);
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

    public function setMemberRepository(Repository $repository): void
    {
        $this->memberRepository = $repository;
    }

    public function setEventRepository(Repository $eventRepository): void
    {
        $this->eventRepository = $eventRepository;
    }

    public function getMemberRepository(): Repository
    {
        return $this->memberRepository;
    }

    public function getEventRepository(): Repository
    {
        return $this->eventRepository;
    }

    public function setGuildRepository(Repository $guildRepository): void
    {
        $this->guildRepository = $guildRepository;
    }

    public function getGuildRepository(): Repository
    {
        return $this->guildRepository;
    }

    public function setThreadRepository(Repository $threadRepository): void
    {
        $this->threadRepository = $threadRepository;
    }

    public function getThreadRepository(): Repository
    {
        return $this->threadRepository;
    }

    public function getTwig(): Environment
    {
        return $this->twig;
    }
}