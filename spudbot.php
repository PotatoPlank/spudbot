<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

use Discord\WebSockets\Intents;
use Doctrine\DBAL\DriverManager;
use Spudbot\Bindable\Event\Reactions\MessageHasManyReactions;
use Spudbot\Bot\Spud;
use Spudbot\Bot\SpudOptions;
use Spudbot\Repository\SQL\EventRepository;
use Spudbot\Repository\SQL\GuildRepository;
use Spudbot\Repository\SQL\MemberRepository;
use Spudbot\Repository\SQL\ThreadRepository;


require_once "vendor/autoload.php";

if (!isset($_ENV['DOCKER'])) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}
if (!isset($_ENV['DATABASE_NAME'])) {
    exit('Invalid config, database not detected');
}

$connectionParams = [
    'dbname' => $_ENV['DATABASE_NAME'],
    'user' => $_ENV['DATABASE_USERNAME'],
    'password' => $_ENV['DATABASE_PASSWORD'],
    'host' => $_ENV['DATABASE_HOST'],
    'driver' => $_ENV['DATABASE_DRIVER'],
];
$dbal = DriverManager::getConnection($connectionParams);

$options = new SpudOptions($_ENV['DISCORD_TOKEN']);
$options->setIntents(Intents::getDefaultIntents() | Intents::MESSAGE_CONTENT | Intents::GUILD_MEMBERS);
$options->shouldLoadAllMembers();

$spud = new Spud($options);

$spud->setDoctrineClient($dbal);
$spud->setMemberRepository(new MemberRepository($dbal));
$spud->setEventRepository(new EventRepository($dbal));
$spud->setGuildRepository(new GuildRepository($dbal));
$spud->setThreadRepository(new ThreadRepository($dbal));

$spud->loadBindableCommandDirectory(__DIR__ . '/src/Spudbot/Bindable/Command');

$excludedEvents = [
    MessageHasManyReactions::class,
];
$spud->loadBindableEventDirectory(__DIR__ . '/src/Spudbot/Bindable/Event', $excludedEvents);


$spud->run();