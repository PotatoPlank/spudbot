<?php

/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024-2025. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

use Discord\Discord;
use Discord\WebSockets\Intents;
use Monolog\ErrorHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Level;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Sentry\Monolog\BreadcrumbHandler;
use Sentry\Monolog\Handler;
use Sentry\SentrySdk;
use Spudbot\Bot\Spud;
use Spudbot\Bot\SpudOptions;
use Spudbot\Hydrator\EntityHydrator;
use Spudbot\Parsers\DirectoryParser;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

use function Sentry\init;

return [
    'spud.token' => $_ENV['DISCORD_TOKEN'],
    'spud.options' => function (ContainerInterface $c) {
        $options = new SpudOptions($c->get('spud.token'));
        $options->setIntents(
            Intents::getAllIntents() & ~Intents::GUILD_PRESENCES
        );
        $options->shouldLoadAllMembers();
        return $options;
    },
    'spud.twig' => function () {
        $loader = new FilesystemLoader(dirname(__DIR__) . '/views');
        return new Environment($loader);
    },
    'spud.events' => function () {
        return dirname(__DIR__) . '/Spudbot/Events';
    },
    'spud.commands' => function () {
        return dirname(__DIR__) . '/Spudbot/Commands';
    },
    Discord::class => DI\factory(function (ContainerInterface $c) {
        return new Discord($c->get('spud.options')->getOptions());
    }),
    Spud::class => DI\autowire(),
    DirectoryParser::class => DI\autowire(),
    EntityHydrator::class => DI\autowire(),
    Logger::class => function () {
        $logger = new Logger('spudbot');
        $logger->pushHandler(new RotatingFileHandler(dirname(__DIR__, 2) . '/logs/spudbot.log', 7));
        if (!empty($_ENV['SENTRY_DSN'])) {
            init([
                'dsn' => $_ENV['SENTRY_DSN'],
                'environment' => $_ENV['SENTRY_ENV'],
            ]);
            $logger->pushHandler(
                new BreadcrumbHandler(
                    hub: SentrySdk::getCurrentHub(),
                    level: Level::Info, // Take note of the level here, messages with that level or higher will be attached to future Sentry events as breadcrumbs
                )
            );
            $logger->pushHandler(
                new Handler(
                    hub: SentrySdk::getCurrentHub(),
                    level: Level::Error,
                    // Take note of the level here, messages with that level or higher will be sent to Sentry
                    bubble: true,
                    fillExtraContext: false, // Will add a `monolog.context` & `monolog.extra`, key to the event with the Monolog `context` & `extra` values
                )
            );
        }
        ErrorHandler::register($logger);
        return $logger;
    }
];

