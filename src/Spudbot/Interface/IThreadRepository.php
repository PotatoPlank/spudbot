<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

declare(strict_types=1);

namespace Spudbot\Interface;

use Spudbot\Helpers\Collection;
use Spudbot\Model\Guild;
use Spudbot\Model\Thread;

abstract class IThreadRepository
{
    abstract public function findById(string|int $id): Thread;

    abstract public function findByDiscordId(string $discordId, string $discordGuildId): Thread;

    abstract public function findByPart(\Discord\Parts\Thread\Thread $thread): Thread;

    abstract public function findByGuild(Guild $guild): Collection;

    abstract public function getAll(): Collection;

    abstract public function save(Thread $thread): bool;

    abstract public function remove(Thread $thread): bool;
}