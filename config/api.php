<?php

/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

use GuzzleHttp\Client;
use Psr\Container\ContainerInterface;
use Spudbot\Repository\Api\ChannelRepository;
use Spudbot\Repository\Api\DirectoryRepository;
use Spudbot\Repository\Api\EventRepository;
use Spudbot\Repository\Api\GuildRepository;
use Spudbot\Repository\Api\MemberRepository;
use Spudbot\Repository\Api\ReminderRepository;
use Spudbot\Repository\Api\ThreadRepository;

return [
    'api.endpoint' => $_ENV['API_ENDPOINT'],
    'api.token' => $_ENV['API_TOKEN'],
    Client::class => function (ContainerInterface $c) {
        return new Client([
            'base_uri' => $c->get('api.endpoint'),
            'headers' => [
                'Authorization' => "Bearer {$c->get('api.token')}",
                'Content-Type' => "application/json",
                'Accept' => "application/json",
            ],
        ]);
    },
    MemberRepository::class => DI\autowire(),
    EventRepository::class => DI\autowire(),
    GuildRepository::class => DI\autowire(),
    ThreadRepository::class => DI\autowire(),
    ChannelRepository::class => DI\autowire(),
    ReminderRepository::class => DI\autowire(),
    DirectoryRepository::class => DI\autowire(),
];

