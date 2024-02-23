<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023-2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

use DI\ContainerBuilder;
use Spudbot\Bindable\Event\Member\MemberBanned;
use Spudbot\Bindable\Event\Reactions\MessageHasManyReactions;
use Spudbot\Bindable\Event\Thread\DeletedThread;
use Spudbot\Bot\Spud;


require_once "vendor/autoload.php";

if (!isset($_ENV['DOCKER'])) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}
if (!isset($_ENV['DATABASE_NAME'])) {
    exit('Invalid config, database not detected');
}

$builder = new ContainerBuilder();
$builder->addDefinitions(__DIR__ . '/config/api.php');
$builder->addDefinitions(__DIR__ . '/config/bot.php');
$container = $builder->build();

$spud = $container->get(Spud::class);

exit;

$excludedCommands = [

];

//$spud->loadBindableCommandDirectory(__DIR__ . '/src/Spudbot/Bindable/Command');

$excludedEvents = [
    MessageHasManyReactions::class,
    MemberBanned::class,
    DeletedThread::class,
];
//$spud->loadBindableEventDirectory(__DIR__ . '/src/Spudbot/Bindable/Event', $excludedEvents);


//$spud->run();
