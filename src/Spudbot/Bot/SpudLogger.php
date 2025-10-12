<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2025. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Bot;

use Discord\Parts\Channel\Channel;
use Psr\Log\LoggerInterface;

class SpudLogger
{
    public static ?Channel $logChannel;
    protected static self $instance;
    public array $exceptionQueue;

    private function __construct(protected Spud $spud, protected mixed $guildId)
    {
        $exceptionConsoleCallable = static function (string $message) {
            static::logger()->emergency($message);
        };
        $exceptionDiscordMessage = static function (string $message) {
            static::sendChannelMessage('Exception', $message);
        };
        $this->exceptionQueue = [
            $exceptionConsoleCallable,
            $exceptionDiscordMessage,
        ];
        if ($guildId) {
            static::fetchChannel($guildId);
        }
    }

    /**
     * Returns DiscordPHP logger interface for direct interface
     * @return LoggerInterface
     */
    public static function logger(): LoggerInterface
    {
        return static::$instance->spud->log();
    }

    protected static function sendChannelMessage(string $title, string $message, bool $emitTerminate = false): void
    {
        $builder = static::getInstance()
            ->spud->interact()
            ->setTitle($title)
            ->setDescription($message);

        if (!isset(static::$logChannel)) {
            static::debug("Unable to send message, channel unknown. $message");
            return;
        }

        static::$logChannel->sendMessage($builder->build())->then(onRejected: function () {
            static::error('Failed sending discord message.');
        })->finally(function () use ($emitTerminate) {
            if ($emitTerminate) {
                static::getInstance()->spud->discord->getLoop()->addTimer(1, function () {
                    static::debug('Emitting discord terminate.');
                    static::getInstance()->spud->discord->emit(Events::TERMINATE->value);
                });
            }
        });
    }

    /**
     * Returns instance of the bot wrapper
     * @param Spud|null $spud
     * @param mixed|null $guildId
     * @return static
     */
    public static function getInstance(?Spud $spud = null, mixed $guildId = null): static
    {
        if (!isset(static::$instance)) {
            static::$instance = new static($spud, $guildId);
        }
        return static::$instance;
    }

    /**
     * Sends debug message to console
     * @param string $message
     * @return void
     */
    public static function debug(string $message): void
    {
        static::logger()->debug($message);
    }

    /**
     * Sends error to console
     * @param string $message
     * @return void
     */
    public static function error(string $message): void
    {
        static::logger()->error($message);
    }

    public static function fetchChannel(mixed $guildId): void
    {
        // Wait for injection
        if (!isset(static::getInstance()->spud->discord)) {
            return;
        }
        $guild = static::getInstance()->spud->discord->guilds->get('id', $guildId);
        if (!$guild) {
            return;
        }
        $channel = $guild->channels->get('id', '1426677723413348522');
        if ($channel) {
            static::$logChannel = $channel;
        }
    }

    /**
     * Sends notice to console and discord logger channel
     * @param string $message
     * @return void
     */
    public static function notice(string $message): void
    {
        static::logger()->notice($message);
        static::sendChannelMessage('Notice', $message);
    }

    /**
     * Sends exception to exception queue (console, discord, etc)
     * @param string $message
     * @param bool $closeLoop - Signals the react loop to close
     * @return void
     */
    public static function exception(string $message, bool $closeLoop = false): void
    {
        if ($closeLoop) {
            static::debug('Attempting to close gracefully.');
            static::getInstance()->spud->discord->on(Events::TERMINATE->value, function () {
                static::getInstance()->spud->discord->close();
            });
        }
        foreach (static::getInstance()->exceptionQueue as $callable) {
            $callable($message);
        }
    }
}
