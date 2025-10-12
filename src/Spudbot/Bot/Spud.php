<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023-2025. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Bot;

use Carbon\Carbon;
use DI\Attribute\Inject;
use Discord\Discord;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Spudbot\Builder\CommandBuilder;
use Spudbot\Builder\EmbeddedResponse;
use Spudbot\Builder\OptionBuilder;
use Spudbot\Exception\BotTerminationException;
use Spudbot\Handler\ErrorQueue;
use Spudbot\Handler\ExceptionQueue;
use Spudbot\Handler\SentryExceptions;
use Spudbot\Handler\TerminationHandler;
use Spudbot\Model\Guild;
use Spudbot\Services\GuildService;
use Spudbot\Util\Filesystem;
use Twig\Environment;

use function Sentry\captureLastError;

class Spud
{
    public readonly ?Guild $logGuild;
    #[Inject('spud.twig')]
    public readonly Environment $twig;
    #[Inject]
    public readonly Discord $discord;
    #[Inject]
    public readonly CommandObserver $commandObserver;
    #[Inject]
    public readonly EventObserver $eventObserver;
    public readonly Carbon $startedAt;
    #[Inject]
    protected GuildService $guildService;

    public function __construct(public readonly ?ContainerInterface $container)
    {
        date_default_timezone_set('UTC');

        $errorHandler = new ErrorQueue();
        $exceptionHandler = new ExceptionQueue();
        if (!empty($_ENV['SENTRY_DSN'])) {
            $sentryHandler = new SentryExceptions($_ENV['SENTRY_DSN'], $_ENV['SENTRY_ENV']);
            $exceptionHandler->addHandler([$sentryHandler, 'handler']);
            $errorHandler->addHandler(function () {
                captureLastError();
            });
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

            $files->filter(function ($event) {
                return !str_contains($event, '\Abstract');
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
            $this->container->injectOn($subscriber);
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
        //SpudLogger::getInstance($this, $_ENV['LOG_GUILD'] ?? null);
        if (isset($_ENV['LOG_GUILD'])) {
            $id = $_ENV['LOG_GUILD'];
            if (!empty($id)) {
                $this->logGuild = $this->guildService->findByDiscordId($id);
            }
        }

        $boot = new Boot($this);
        $boot->hook();
        $this->discord->on(Events::READY->value, function () {
            $this->eventObserver->emit(Events::READY->value);
            //SpudLogger::notice('SpudBot started.');
        });

        $this->startedAt = Carbon::now();
        $this->discord->run();


        if (!empty($this->logGuild)) {
            $guild = $this->discord->guilds->get('id', $this->logGuild->discordId);
            if (!$guild) {
                return;
            }
            $output = $this->logGuild->getChannelThreadPart(Guild::BOT_LOG_CHANNEL, $guild);
            $this->interact()
                ->setTitle('Bot Started')
                ->setDescription("Bot started at {$this->startedAt->toIso8601String()}")
                ->sendTo($output);
        }
    }

    public function interact(): EmbeddedResponse
    {
        return $this->container->injectOn(new EmbeddedResponse($this->discord));
    }

    public function command(string $name, string $description = CommandBuilder::DEFAULT_DESCRIPTION): CommandBuilder
    {
        return $this->container->injectOn(new CommandBuilder($name, $description));
    }

    public function log(): LoggerInterface
    {
        return $this->discord->getLogger();
    }


    public function commandOption(
        string $name,
        string $description = OptionBuilder::DEFAULT_DESCRIPTION
    ): OptionBuilder {
        return $this->container->injectOn(new OptionBuilder($name, $description));
    }

}
