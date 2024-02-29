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
use Spudbot\Builder\EmbeddedResponse;
use Spudbot\Exception\BotTerminationException;
use Spudbot\Handler\ExceptionQueue;
use Spudbot\Handler\SentryExceptions;
use Spudbot\Handler\TerminationHandler;
use Spudbot\Model\Guild;
use Spudbot\Repository\Api\ChannelRepository;
use Spudbot\Repository\Api\DirectoryRepository;
use Spudbot\Repository\Api\EventRepository;
use Spudbot\Repository\Api\GuildRepository;
use Spudbot\Repository\Api\MemberRepository;
use Spudbot\Repository\Api\ReminderRepository;
use Spudbot\Repository\Api\ThreadRepository;
use Spudbot\Util\Filesystem;
use Twig\Environment;

class Spud
{
    public readonly ?Guild $logGuild;
    #[Inject]
    public readonly Environment $twig;
    public readonly Discord $discord;
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
    #[Inject]
    public readonly CommandObserver $commandObserver;
    #[Inject]
    public readonly EventObserver $eventObserver;

    public function __construct(SpudOptions $options)
    {
        date_default_timezone_set('UTC');
        $this->discord = new Discord($options->getOptions());
        $exceptionHandler = new ExceptionQueue();
        if (!empty($_ENV['SENTRY_DSN'])) {
            $sentryHandler = new SentryExceptions($_ENV['SENTRY_DSN'], $_ENV['SENTRY_ENV']);
            $exceptionHandler->addHandler([$sentryHandler, 'handler']);
        }
        $terminationHandler = new TerminationHandler();
        $exceptionHandler->addHandler([$terminationHandler, 'handler']);
    }

    public function attachAll(string $directory, array $excluded = []): void
    {
        $files = Filesystem::fetchFilesByDirectoryRecursively(realpath($directory));

        if (!$files->empty()) {
            $files->transform(function ($event) use ($directory) {
                return Filesystem::getNamespaceFromPath($directory . '\\' . $event);
            });

            if (!empty($excluded)) {
                $files->filter(function ($event) use ($excluded) {
                    return !in_array($event, $excluded, true);
                });
            }

            foreach ($files as $file) {
                $this->attachSubscriber($file);
            }
        }
    }

    public function attachSubscriber(string $name): void
    {
        if ($name !== Boot::class) {
            $subscriber = new $name($this);
            $subscriber->hook();
        }
    }

    public function terminate(string $message = ''): void
    {
        $this->discord->removeAllListeners();
        $this->discord->close();
        throw new BotTerminationException($message);
    }

    public function run(): void
    {
        $boot = new Boot($this);
        $boot->hook();
        $this->discord->on('ready', function () {
            $this->eventObserver->emit('ready');
        });


        $this->discord->run();


        if (!empty($this->logGuild)) {
            $channelId = $this->logGuild->getOutputChannelId();
            $threadId = $this->logGuild->getOutputThreadId();
            $guild = $this->discord->guilds->get('id', $this->logGuild->getDiscordId());
            if (!$guild) {
                return;
            }
            $output = $guild->channels->get('id', $channelId);
            if (!empty($threadId)) {
                $output = $output->threads->get('id', $threadId);
            }
            if (!$output) {
                return;
            }
            $builder = $this->getSimpleResponseBuilder();
            $builder->setTitle('Bot Started');
            $builder->setDescription('Bot started at ' . Carbon::now()->toIso8601String());
            $output->sendMessage($builder->getEmbeddedMessage());
        }
    }

    public function getSimpleResponseBuilder(): EmbeddedResponse
    {
        return new EmbeddedResponse($this->discord);
    }

}
