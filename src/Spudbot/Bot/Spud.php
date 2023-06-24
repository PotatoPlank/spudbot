<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Bot;

use Carbon\Carbon;
use Discord\Discord;
use Doctrine\DBAL\Connection;
use Spudbot\Bindable\Event\OnReadyExecuteBinds;
use Spudbot\Builder\EmbeddedResponse;
use Spudbot\Exception\BotTerminationException;
use Spudbot\Helpers\Collection;
use Spudbot\Interface\IBindableCommand;
use Spudbot\Interface\IBindableEvent;
use Spudbot\Interface\IEventRepository;
use Spudbot\Interface\IGuildRepository;
use Spudbot\Interface\IMemberRepository;
use Spudbot\Interface\IThreadRepository;
use Spudbot\Model\Guild;
use Spudbot\Util\Filesystem;
use Throwable;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

use function Sentry\captureException;
use function Sentry\init;

class Spud
{
    public const MAJOR = 1;
    public const MINOR = 1;
    public const REVISION = 0;
    public const BUILD = null;
    public ?Guild $logGuild;
    private Discord $discord;
    private Collection $events;
    private Collection $commands;
    private ?Connection $dbal;
    private IMemberRepository $memberRepository;
    private IEventRepository $eventRepository;
    private IGuildRepository $guildRepository;
    private IThreadRepository $threadRepository;
    private Environment $twig;

    public function __construct(SpudOptions $options)
    {
        $this->discord = new Discord($options->getOptions());
        $this->events = new Collection();
        $this->commands = new Collection();
        $loader = new FilesystemLoader(dirname(__DIR__, 2) . '/views');
        $this->twig = new Environment($loader);

        if (!empty($_ENV['SENTRY_DSN'])) {
            init(['dsn' => $_ENV['SENTRY_DSN'], 'environment' => $_ENV['SENTRY_ENV']]);
        }

        set_exception_handler(function (Throwable $exception) {
            if (!$exception instanceof BotTerminationException || !empty($exception->getMessage())) {
                if (!empty($_ENV['SENTRY_DSN'])) {
                    captureException($exception);
                }
                print "An exception was encountered and the bot stopped: {$exception->getFile()}:{$exception->getLine()} {$exception->getMessage()}" . PHP_EOL;
                exit();
            }

            print "Bot killed." . PHP_EOL;
            exit();
        });
    }

    public static function getVersionString(): string
    {
        $version = sprintf('v%d.%d.%d', self::MAJOR, self::MINOR, self::REVISION);

        if (!empty(self::BUILD)) {
            $version .= '-' . self::BUILD;
        }

        return $version;
    }

    public function loadBindableCommandDirectory(string $path, array $excludedCommands = []): void
    {
        $files = Filesystem::fetchFilesByDirectoryRecursively(realpath($path));
        if (!$files->empty()) {
            $files->transform(function ($event) use ($path) {
                return Filesystem::getNamespaceFromPath($path . '\\' . $event);
            });
            if (!empty($excludedCommands)) {
                $files->filter(function ($command) use ($excludedCommands) {
                    return !in_array($command, $excludedCommands);
                });
            }

            foreach ($files as $file) {
                $this->loadBindableCommand(new $file());
            }
        }
    }

    public function loadBindableCommand(IBindableCommand $command): void
    {
        $command->setDiscordClient($this->discord);
        $command->setSpudClient($this);
        if (!empty($this->dbal)) {
            $command->setDoctrineClient($this->dbal);
        }

        $this->commands->push($command);
    }

    public function setDoctrineClient(?Connection $dbal): void
    {
        $this->dbal = $dbal;
    }

    public function loadBindableEventDirectory(string $path, array $excludedEvents = []): void
    {
        $files = Filesystem::fetchFilesByDirectoryRecursively(realpath($path));

        if (!$files->empty()) {
            $files->transform(function ($event) use ($path) {
                return Filesystem::getNamespaceFromPath($path . '\\' . $event);
            });

            if (!empty($excludedEvents)) {
                $files->filter(function ($event) use ($excludedEvents) {
                    return !in_array($event, $excludedEvents);
                });
            }

            foreach ($files as $file) {
                $this->loadBindableEvent(new $file());
            }
        }
    }

    public function loadBindableEvent(IBindableEvent $event): void
    {
        $event->setDiscordClient($this->discord);
        $event->setSpudClient($this);
        if (!empty($this->dbal)) {
            $event->setDoctrineClient($this->dbal);
        }

        if (!isset($this->events[$event->getBoundEvent()])) {
            $this->events->set($event->getBoundEvent(), new Collection());
        }
        $this->events->get($event->getBoundEvent())
            ->push($event);
    }

    public function kill(string $message = ''): void
    {
        $this->discord->close();
        throw new BotTerminationException($message);
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

        if (!empty($this->logGuild)) {
            $channelId = $this->logGuild->getOutputChannelId();
            $threadId = $this->logGuild->getOutputThreadId();
            $guild = $this->discord->guilds->get('id', $this->logGuild->getDiscordId());
            $output = $guild->channels->get('id', $channelId);
            if (!empty($threadId)) {
                $output = $output->threads->get('id', $threadId);
            }
            $builder = $this->getSimpleResponseBuilder();
            $builder->setTitle('Bot Started');
            $builder->setDescription('Bot started at ' . Carbon::now()->toIso8601String());
            $output->sendMessage($builder->getEmbeddedMessage());
        }
    }

    public function on(string $event, callable $listener): Discord
    {
        return $this->discord->on($event, $listener);
    }

    public function getSimpleResponseBuilder(): EmbeddedResponse
    {
        return new EmbeddedResponse($this->discord);
    }

    public function getMemberRepository(): IMemberRepository
    {
        return $this->memberRepository;
    }

    public function setMemberRepository(IMemberRepository $repository): void
    {
        $this->memberRepository = $repository;
    }

    public function getEventRepository(): IEventRepository
    {
        return $this->eventRepository;
    }

    public function setEventRepository(IEventRepository $eventRepository): void
    {
        $this->eventRepository = $eventRepository;
    }

    public function getGuildRepository(): IGuildRepository
    {
        return $this->guildRepository;
    }

    public function setGuildRepository(IGuildRepository $guildRepository): void
    {
        $this->guildRepository = $guildRepository;
        if (!empty($_ENV['LOG_GUILD'])) {
            $this->logGuild = $this->guildRepository->findById($_ENV['LOG_GUILD']);
        }
    }

    public function getThreadRepository(): IThreadRepository
    {
        return $this->threadRepository;
    }

    public function setThreadRepository(IThreadRepository $threadRepository): void
    {
        $this->threadRepository = $threadRepository;
    }

    public function getTwig(): Environment
    {
        return $this->twig;
    }

}