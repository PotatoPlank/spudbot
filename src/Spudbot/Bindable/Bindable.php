<?php

namespace Spudbot\Bindable;

use Discord\Discord;
use Doctrine\DBAL\Connection;
use Spudbot\Bot\Spud;

abstract class Bindable
{
    protected Discord $discord;
    protected ?Connection $dbal;
    protected Spud $spud;

    public function setDiscordClient(Discord $discord): void
    {
        $this->discord = $discord;
    }

    public function setDoctrineClient(?Connection $dbal): void
    {
        $this->dbal = $dbal;
    }

    public function setSpudClient(Spud $spud): void
    {
        $this->spud = $spud;
    }
}