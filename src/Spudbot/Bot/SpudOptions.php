<?php

namespace Spudbot\Bot;

use Discord\Discord;
use Discord\WebSockets\Intents;

class SpudOptions
{
    private array $options;

    public function __construct(string $token)
    {
        $this->setToken($token);
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setToken(string $token): void
    {
        $this->options['token'] = $token;
    }

    public function setIntents(array|int $intents): void
    {
        $this->options['intents'] = $intents;
    }

    public function shouldLoadAllMembers($boolean = true): void
    {
        $this->options['loadAllMembers'] = $boolean;
    }

    public function setRawOption(string $key, string $value): void
    {
        $this->options[$key] = $value;
    }
}