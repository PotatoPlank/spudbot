<?php

/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

use Discord\WebSockets\Intents;
use Psr\Container\ContainerInterface;
use Spudbot\Bot\CommandObserver;
use Spudbot\Bot\EventObserver;
use Spudbot\Bot\Spud;
use Spudbot\Bot\SpudOptions;
use Spudbot\Repository\Api\ChannelRepository;
use Spudbot\Repository\Api\DirectoryRepository;
use Spudbot\Repository\Api\EventRepository;
use Spudbot\Repository\Api\GuildRepository;
use Spudbot\Repository\Api\MemberRepository;
use Spudbot\Repository\Api\ReminderRepository;
use Spudbot\Repository\Api\ThreadRepository;
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
        $loader = new FilesystemLoader(dirname(__DIR__) . '/src/views');
        return new Environment($loader);
    },
    Spud::class => DI\autowire()
        ->constructor(DI\get('spud.options'))
        ->property('memberRepository', DI\get(MemberRepository::class))
        ->property('eventRepository', DI\get(EventRepository::class))
        ->property('guildRepository', DI\get(GuildRepository::class))
        ->property('threadRepository', DI\get(ThreadRepository::class))
        ->property('channelRepository', DI\get(ChannelRepository::class))
        ->property('reminderRepository', DI\get(ReminderRepository::class))
        ->property('directoryRepository', DI\get(DirectoryRepository::class))
        ->property('commandObserver', DI\autowire(CommandObserver::class))
        ->property('eventObserver', DI\autowire(EventObserver::class))
        ->property('twig', DI\get('spud.twig')),
];

