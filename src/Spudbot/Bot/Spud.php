<?php

namespace Spudbot\Bot;

use Carbon\Carbon;
use Discord\Discord;
use Discord\WebSockets\Event;
use Doctrine\DBAL\Connection;
use Spudbot\Bindable\Event\OnReadyExecuteBinds;
use Spudbot\Builder\EmbeddedResponse;
use Spudbot\Exception\BotTerminationException;
use Spudbot\Helpers\Collection;
use Spudbot\Interface\IBindableCommand;
use Spudbot\Interface\IBindableEvent;
use Spudbot\Interface\IEventRepository;
use Spudbot\Interface\IGuildRepository;
use Spudbot\Interface\IMemberRepository;
use Spudbot\Interface\IThreadRepository;
use Spudbot\Model\Guild;
use Throwable;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

use function Sentry\captureException;
use function Sentry\init;

class Spud
{
    public const MAJOR = 1;
    public const MINOR = 0;
    public const REVISION = 4;
    public const BUILD = null;
    private Discord $discord;
    private Collection $events;
    private Collection $commands;
    private ?Connection $dbal;
    private IMemberRepository $memberRepository;
    private IEventRepository $eventRepository;
    private IGuildRepository $guildRepository;
    private IThreadRepository $threadRepository;
    private Environment $twig;
    public ?Guild $logGuild;


    public function __construct(SpudOptions $options)
    {
        $this->discord = new Discord($options->getOptions());
        $this->events = new Collection();
        $this->commands = new Collection();
        $loader = new FilesystemLoader(dirname(__DIR__, 2) . '/views');
        $this->twig = new Environment($loader);

        if(!empty($_ENV['SENTRY_DSN'])){
            init(['dsn' => $_ENV['SENTRY_DSN'], 'environment' => $_ENV['SENTRY_ENV'] ]);
        }

        set_exception_handler(function (Throwable $exception){
            if(!$exception instanceof BotTerminationException || !empty($exception->getMessage())){
                if(!empty($_ENV['SENTRY_DSN'])){
                    captureException($exception);
                }
                print "An exception was encountered and the bot stopped: {$exception->getFile()}:{$exception->getLine()} {$exception->getMessage()}" . PHP_EOL;
                exit();
            }

            print "Bot killed." . PHP_EOL;
            exit();
        });
    }

    public function setDoctrineClient(?Connection $dbal): void
    {
        $this->dbal = $dbal;
    }

    public function loadBindableCommand(IBindableCommand $command): void
    {
        $command->setDiscordClient($this->discord);
        $command->setSpudClient($this);
        if(!empty($this->dbal)){
            $command->setDoctrineClient($this->dbal);
        }

        $this->commands->push($command);
    }

    public function loadBindableEvent(IBindableEvent $event): void
    {
        $event->setDiscordClient($this->discord);
        $event->setSpudClient($this);
        if(!empty($this->dbal)){
            $event->setDoctrineClient($this->dbal);
        }

        if(!isset($this->events[$event->getBoundEvent()])){
            $this->events->set($event->getBoundEvent(), new Collection());
        }
        $this->events->get($event->getBoundEvent())
            ->push($event);
    }

    public function getSimpleResponseBuilder(): EmbeddedResponse
    {
        return new EmbeddedResponse($this->discord);
    }

    public function on(string $event, callable $listener): Discord
    {
        return $this->discord->on($event, $listener);
    }

    public function run(): void
    {
        $onReadyEvent = new OnReadyExecuteBinds();
        $onReadyEvent->setDiscordClient($this->discord);
        $onReadyEvent->setSpudClient($this);
        $onReadyEvent->setEventCollection($this->events);
        $onReadyEvent->setCommandCollection($this->commands);

        $this->discord->on($onReadyEvent->getBoundEvent(), $onReadyEvent->getListener())
            ->run();

        if(!empty($this->logGuild)){
            $channelId = $this->logGuild->getOutputChannelId();
            $threadId = $this->logGuild->getOutputThreadId();
            $guild = $this->discord->guilds->get('id', $this->logGuild->getDiscordId());
            $output = $guild->channels->get('id', $channelId);
            if(!empty($threadId)){
                $output = $output->threads->get('id', $threadId);
            }
            $builder = $this->getSimpleResponseBuilder();
            $builder->setTitle('Bot Started');
            $builder->setDescription('Bot started at ' . Carbon::now()->toIso8601String());
            $output->sendMessage($builder->getEmbeddedMessage());
        }
    }

    public function setMemberRepository(IMemberRepository $repository): void
    {
        $this->memberRepository = $repository;
    }

    public function getMemberRepository(): IMemberRepository
    {
        return $this->memberRepository;
    }

    public function setEventRepository(IEventRepository $eventRepository): void
    {
        $this->eventRepository = $eventRepository;
    }

    public function getEventRepository(): IEventRepository
    {
        return $this->eventRepository;
    }

    public function setGuildRepository(IGuildRepository $guildRepository): void
    {
        $this->guildRepository = $guildRepository;
        if(!empty($_ENV['LOG_GUILD'])){
            $this->logGuild = $this->guildRepository->findById($_ENV['LOG_GUILD']);
        }
    }

    public function getGuildRepository(): IGuildRepository
    {
        return $this->guildRepository;
    }

    public function setThreadRepository(IThreadRepository $threadRepository): void
    {
        $this->threadRepository = $threadRepository;
    }

    public function getThreadRepository(): IThreadRepository
    {
        return $this->threadRepository;
    }

    public function getTwig(): Environment
    {
        return $this->twig;
    }

    public function kill(string $message = ''): void
    {
        throw new BotTerminationException($message);
    }

    public static function getVersionString(): string
    {
        $version = sprintf('v%d.%d.%d', self::MAJOR, self::MINOR, self::REVISION);

        if(!empty(self::BUILD)){
            $version .= '-' . self::BUILD;
        }

        return $version;
    }

}