<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023-2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

use Discord\WebSockets\Intents;
use GuzzleHttp\Client;
use Spudbot\Bindable\Event\Member\MemberBanned;
use Spudbot\Bindable\Event\Reactions\MessageHasManyReactions;
use Spudbot\Bindable\Event\Thread\DeletedThread;
use Spudbot\Bot\Spud;
use Spudbot\Bot\SpudOptions;
use Spudbot\Repository\Api\ChannelRepository;
use Spudbot\Repository\Api\DirectoryRepository;
use Spudbot\Repository\Api\EventRepository;
use Spudbot\Repository\Api\GuildRepository;
use Spudbot\Repository\Api\MemberRepository;
use Spudbot\Repository\Api\ReminderRepository;
use Spudbot\Repository\Api\ThreadRepository;


require_once "vendor/autoload.php";

if (!isset($_ENV['DOCKER'])) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}
if (!isset($_ENV['DATABASE_NAME'])) {
    exit('Invalid config, database not detected');
}

$client = new Client([
    'base_uri' => $_ENV['API_ENDPOINT'],
    'headers' => [
        'Authorization' => "Bearer {$_ENV['API_TOKEN']}",
        'Content-Type' => "application/json",
        'Accept' => "application/json",
    ],
]);

$options = new SpudOptions($_ENV['DISCORD_TOKEN']);
$options->setIntents(
    Intents::getAllIntents() & ~Intents::GUILD_PRESENCES
);
$options->shouldLoadAllMembers();

$spud = new Spud($options);

$spud->setGuzzleClient($client);
$spud->setMemberRepository(new MemberRepository($client));
$spud->setEventRepository(new EventRepository($client));
$spud->setGuildRepository(new GuildRepository($client));
$spud->setThreadRepository(new ThreadRepository($client));
$spud->setChannelRepository(new ChannelRepository($client));
$spud->setReminderRepository(new ReminderRepository($client));
$spud->setDirectoryRepository(new DirectoryRepository($client));

$excludedCommands = [

];

//$spud->loadBindableCommandDirectory(__DIR__ . '/src/Spudbot/Bindable/Command');

$excludedEvents = [
    MessageHasManyReactions::class,
    MemberBanned::class,
    DeletedThread::class,
];
//$spud->loadBindableEventDirectory(__DIR__ . '/src/Spudbot/Bindable/Event', $excludedEvents);


$spud->run();
