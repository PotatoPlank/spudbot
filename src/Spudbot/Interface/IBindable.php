<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

declare(strict_types=1);

namespace Spudbot\Interface;

use Discord\Discord;
use Doctrine\DBAL\Connection;
use Spudbot\Bot\Spud;

abstract class IBindable
{
    protected Discord $discord;
    protected ?Connection $dbal;
    protected Spud $spud;
    protected bool $requirements = true;

    /**
     * @param Discord $discord
     * @return void
     * @deprecated v1.2.0 Removing accessors and mutators in favor of readonly properties
     * @see Spud::$discord
     */
    public function setDiscordClient(Discord $discord): void
    {
        $this->discord = $discord;
    }

    /**
     * @param Connection|null $dbal
     * @return void
     * @deprecated v1.2.0 Removing accessors and mutators in favor of readonly properties
     * @see Spud::$dbal
     */
    public function setDoctrineClient(?Connection $dbal): void
    {
        $this->dbal = $dbal;
    }

    public function setSpudClient(Spud $spud): void
    {
        $this->spud = $spud;
    }

    public function checkRequirements(): void
    {
        if ($this->requirements !== true) {
            throw new \RuntimeException(__CLASS__ . ' did not have it\'s requirements met to be run.');
        }
    }
}