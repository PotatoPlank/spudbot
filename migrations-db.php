<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

if (!isset($_ENV['DOCKER'])) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

return [
    'dbname' => $_ENV['DATABASE_NAME'],
    'user' => $_ENV['DATABASE_USERNAME'],
    'password' => $_ENV['DATABASE_PASSWORD'],
    'host' => $_ENV['DATABASE_HOST'],
    'driver' => $_ENV['DATABASE_DRIVER'],
];