<?php

/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

use GuzzleHttp\Client;
use Psr\Container\ContainerInterface;

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
];

