<?php

use Discord\WebSockets\Intents;
use Doctrine\DBAL\DriverManager;
use Spudbot\Bindable\Command\About;
use Spudbot\Bindable\Command\FAQ;
use Spudbot\Bindable\Command\Hype;
use Spudbot\Bindable\Command\MemberCount;
use Spudbot\Bindable\Command\PurgeCommands;
use Spudbot\Bindable\Command\Restart;
use Spudbot\Bindable\Command\Setup;
use Spudbot\Bindable\Command\User;
use Spudbot\Bindable\Command\Verify;
use Spudbot\Bindable\Command\Version;
use Spudbot\Bindable\Event\Member\MemberBanned;
use Spudbot\Bindable\Event\Member\MemberJoins;
use Spudbot\Bindable\Event\Member\MemberLeaves;
use Spudbot\Bindable\Event\Message\ApplyMemberRoleUpgrades;
use Spudbot\Bindable\Event\Message\BotMentioned;
use Spudbot\Bindable\Event\Message\CountMemberComments;
use Spudbot\Bindable\Event\Message\LogThreadActivity;
use Spudbot\Bindable\Event\Reactions\MessageHasManyReactions;
use Spudbot\Bindable\Event\ScheduledEvent\AddedUserToNativeEvent;
use Spudbot\Bindable\Event\ScheduledEvent\AddedUserToNativeSeshEvent;
use Spudbot\Bindable\Event\ScheduledEvent\AddedUserToSeshEvent;
use Spudbot\Bindable\Event\ScheduledEvent\EventCreated;
use Spudbot\Bindable\Event\ScheduledEvent\RemovedUserFromNativeEvent;
use Spudbot\Bindable\Event\Thread\DeletedThread;
use Spudbot\Bot\Spud;
use Spudbot\Bot\SpudOptions;
use Spudbot\Repository\SQL\EventRepository;
use Spudbot\Repository\SQL\GuildRepository;
use Spudbot\Repository\SQL\MemberRepository;
use Spudbot\Repository\SQL\ThreadRepository;


require_once "vendor/autoload.php";

if(!isset($_ENV['DOCKER'])){
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}
if(!isset($_ENV['DATABASE_NAME'])){
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

$spud->loadBindableEvent(new MemberBanned());
$spud->loadBindableEvent(new MemberJoins());
$spud->loadBindableEvent(new MemberLeaves());
$spud->loadBindableEvent(new ApplyMemberRoleUpgrades());
$spud->loadBindableEvent(new BotMentioned());
$spud->loadBindableEvent(new CountMemberComments());
$spud->loadBindableEvent(new LogThreadActivity());
$spud->loadBindableEvent(new MessageHasManyReactions());
$spud->loadBindableEvent(new AddedUserToNativeEvent());
$spud->loadBindableEvent(new AddedUserToNativeSeshEvent());
$spud->loadBindableEvent(new AddedUserToSeshEvent());
$spud->loadBindableEvent(new EventCreated());
$spud->loadBindableEvent(new RemovedUserFromNativeEvent());
$spud->loadBindableEvent(new DeletedThread());

$spud->loadBindableCommand(new About());
$spud->loadBindableCommand(new FAQ());
$spud->loadBindableCommand(new Hype());
$spud->loadBindableCommand(new MemberCount());
$spud->loadBindableCommand(new PurgeCommands());
$spud->loadBindableCommand(new Restart());
$spud->loadBindableCommand(new Setup());
$spud->loadBindableCommand(new User());
$spud->loadBindableCommand(new Verify());
$spud->loadBindableCommand(new Version());


$spud->run();