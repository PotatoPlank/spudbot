<?php

namespace Spudbot\Bot;

use Discord\Discord;

class Spud
{
    private Discord $discord;

    public function __construct(SpudOptions $options)
    {
        $this->discord = new Discord($options->getOptions());
    }

    public function on(string $event, callable $listener): Discord
    {
        return $this->discord->on($event, $listener);
    }

    public function run(): void
    {
        $this->discord->run();
    }
}