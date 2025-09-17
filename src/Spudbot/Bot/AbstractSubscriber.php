<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Bot;

use Discord\Parts\User\Member;
use Discord\Parts\User\User;

abstract class AbstractSubscriber
{
    public function __construct(protected Spud $spud)
    {
    }

    abstract public function hook(): void;

    abstract public function update(): void;

    public function canRun(): bool
    {
        return true;
    }

    public function isGuildManager(?Member $member): bool
    {
        return $member?->permissions->manage_guild ?: false;
    }

    public function isBotOwner(?User $user): bool
    {
        return $user && $user->getDiscord()->application->owner->id === $user->id;
    }
}
