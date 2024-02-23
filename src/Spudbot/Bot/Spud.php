<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023-2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Bot;

use Carbon\Carbon;
use DI\Attribute\Inject;
use Discord\Discord;
use Doctrine\DBAL\Connection;
use GuzzleHttp\Client;
use Spudbot\Bindable\Event\OnReadyExecuteBinds;
use Spudbot\Builder\EmbeddedResponse;
use Spudbot\Exception\BotTerminationException;
use Spudbot\Helpers\Collection;
use Spudbot\Interface\IBindableCommand;
use Spudbot\Interface\IBindableEvent;
use Spudbot\Model\Guild;
use Spudbot\Repository\Api\ChannelRepository;
use Spudbot\Repository\Api\DirectoryRepository;
use Spudbot\Repository\Api\EventRepository;
use Spudbot\Repository\Api\GuildRepository;
use Spudbot\Repository\Api\MemberRepository;
use Spudbot\Repository\Api\ReminderRepository;
use Spudbot\Repository\Api\ThreadRepository;
use Spudbot\Util\Filesystem;
use Throwable;
use Twig\Environment;

use function Sentry\captureException;
use function Sentry\init;

class Spud
{
    public const MAJOR = 1;
    public const MINOR = 2;
    public const REVISION = 9;
    public const BUILD = null;
    public readonly ?Guild $logGuild;
    #[Inject]
    public readonly Environment $twig;
    public readonly Discord $discord;
    public readonly ?Connection $dbal;
    public readonly ?Client $client;
    /**
     * @var Collection
     * @deprecated v1.2.0 In v2, this will be replaced with an observer
     */
    public Collection $events;
    /**
     * @var Collection
     * @deprecated v1.2.0 In v2, this will be replaced with an observer
     */
    public Collection $commands;
    #[Inject]
    public readonly MemberRepository $memberRepository;
    #[Inject]
    public readonly EventRepository $eventRepository;
    #[Inject]
    public readonly GuildRepository $guildRepository;
    #[Inject]
    public readonly ThreadRepository $threadRepository;
    #[Inject]
    public readonly ChannelRepository $channelRepository;
    #[Inject]
    public readonly ReminderRepository $reminderRepository;
    #[Inject]
    public readonly DirectoryRepository $directoryRepository;

    public function __construct(SpudOptions $options)
    {
        date_default_timezone_set('UTC');
        $this->discord = new Discord($options->getOptions());
        $this->events = new Collection();
        $this->commands = new Collection();

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
        $command->checkRequirements();
        if (!empty($this->dbal)) {
            $command->setDoctrineClient($this->dbal);
        }

        $this->commands->push($command);
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
        if (!$event instanceof OnReadyExecuteBinds) {
            $event->setDiscordClient($this->discord);
            $event->setSpudClient($this);
            $event->checkRequirements();
            if (!empty($this->dbal)) {
                $event->setDoctrineClient($this->dbal);
            }

            if (!isset($this->events[$event->getBoundEvent()])) {
                $this->events->set($event->getBoundEvent(), new Collection());
            }
            $this->events->get($event->getBoundEvent())
                ->push($event);
        }
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

        $this->discord->on($onReadyEvent->getBoundEvent(), $onReadyEvent->getListener());

        $this->discord->getLoop()
            ->addPeriodicTimer(60, function () {
                $this->discord->emit(Events::EVERY_MINUTE->value);
            });

        $this->discord->run();


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

}
