<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023-2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

use DI\ContainerBuilder;
use Spudbot\Bot\Spud;
use Spudbot\Commands\About;
use Spudbot\Commands\User;
use Spudbot\Events\Member\MemberBanned;
use Spudbot\Events\Reactions\MessageHasManyReactions;
use Spudbot\Events\Ready\ReadyMessage;
use Spudbot\Events\Thread\DeletedThread;


require_once "vendor/autoload.php";

if (!isset($_ENV['DOCKER'])) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}
if (!isset($_ENV['DATABASE_NAME'])) {
    exit('Invalid config, database not detected');
}

$builder = new ContainerBuilder();
$builder->useAttributes(true);
$builder->addDefinitions(
    __DIR__ . '/config/api.php',
    __DIR__ . '/config/bot.php',
    __DIR__ . '/config/repositories.php',
    __DIR__ . '/config/services.php',
);
$container = $builder->build();

$spud = $container->get(Spud::class);

$excludedCommands = [

];

$excludedEvents = [
    MessageHasManyReactions::class,
    MemberBanned::class,
    DeletedThread::class,
];
//$spud->attachAll($container->get('spud.commands'), $excludedCommands);
//$spud->attachAll($container->get('spud.events'), $excludedEvents);

$spud->attachSubscriber(ReadyMessage::class);
$spud->attachSubscriber(About::class);
$spud->attachSubscriber(User::class);


$spud->run();
