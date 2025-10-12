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

    private function __construct(protected Spud $spud)
    {
        $this->spud->discord->getLoop()->addTimer(1, function () {
            static::$logChannel = $this->spud->discord->getChannel('1426677723413348522');
        });
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
     * @return static
     */
    public static function getInstance(?Spud $spud = null): static
    {
        if (!isset(static::$instance)) {
            static::$instance = new static($spud);
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
