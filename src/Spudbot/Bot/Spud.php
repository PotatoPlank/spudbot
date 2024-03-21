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
use Psr\Container\ContainerInterface;
use Spudbot\Builder\EmbeddedResponse;
use Spudbot\Exception\BotTerminationException;
use Spudbot\Handler\ExceptionQueue;
use Spudbot\Handler\SentryExceptions;
use Spudbot\Handler\TerminationHandler;
use Spudbot\Model\Guild;
use Spudbot\Util\Filesystem;
use Twig\Environment;

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

    public function __construct(public readonly ?ContainerInterface $container)
    {
        date_default_timezone_set('UTC');

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
        $boot = new Boot($this);
        $boot->hook();
        $this->discord->on('ready', function () {
            $this->eventObserver->emit('ready');
        });


        $this->discord->run();


        if (!empty($this->logGuild)) {
            $guild = $this->discord->guilds->get('id', $this->logGuild->getDiscordId());
            if (!$guild) {
                return;
            }
            $output = $this->logGuild->getOutputPart($guild);
            $this->interact()
                ->setTitle('Bot Started')
                ->setDescription('Bot started at ' . Carbon::now()->toIso8601String())
                ->sendTo($output);
        }
    }

    public function interact(): EmbeddedResponse
    {
        return new EmbeddedResponse($this->discord);
    }

}
