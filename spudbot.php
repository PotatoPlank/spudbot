<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023-2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

use DI\ContainerBuilder;
use Spudbot\Bot\ConfigurationException;
use Spudbot\Bot\Spud;
use Spudbot\Events\Member\MemberBanned;
use Spudbot\Events\Reactions\MessageHasManyReactions;
use Spudbot\Events\Thread\DeletedThread;


require_once "vendor/autoload.php";

if (!isset($_ENV['DOCKER'])) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}
$configFiles = glob(__DIR__ . '/src/config/*.php');
if (empty($configFiles)) {
    throw new ConfigurationException('No configuration files found.');
}

$builder = new ContainerBuilder();
$builder->useAttributes(true);
foreach ($configFiles as $configFile) {
    $builder->addDefinitions(realpath($configFile));
}
$container = $builder->build();

$spud = $container->get(Spud::class);

$excludedCommands = [

];

$excludedEvents = [
    MessageHasManyReactions::class,
    MemberBanned::class,
    DeletedThread::class,
];
$spud->attachAll($container->get('spud.commands'), $excludedCommands);
$spud->attachAll($container->get('spud.events'), $excludedEvents);

//$spud->attachSubscriber(Spudbot\Events\Message\CountMemberComments::class);
//$spud->attachSubscriber(\Spudbot\Events\Message\LogThreadActivity::class);

//$spud->attachSubscriber(ReadyMessage::class);
//$spud->attachSubscriber(About::class);
//$spud->attachSubscriber(User::class);
//$spud->attachSubscriber(SprayUser::class);


$spud->run();
