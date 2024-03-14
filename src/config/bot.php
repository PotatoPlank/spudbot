<?php

/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

use Discord\Discord;
use Discord\WebSockets\Intents;
use Psr\Container\ContainerInterface;
use Spudbot\Bot\Spud;
use Spudbot\Bot\SpudOptions;
use Spudbot\Parsers\DirectoryParser;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

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
    Discord::class => function (ContainerInterface $c) {
        return new Discord($c->get('spud.options')->getOptions());
    },
    Spud::class => DI\autowire(),
    DirectoryParser::class => DI\autowire(),
];

