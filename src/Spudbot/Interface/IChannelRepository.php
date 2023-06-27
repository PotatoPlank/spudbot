<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

declare(strict_types=1);

namespace Spudbot\Interface;

use Spudbot\Helpers\Collection;
use Spudbot\Model\Channel;
use Spudbot\Model\Guild;

abstract class IChannelRepository
{
    abstract public function findById(string|int $id): Channel;

    abstract public function findByDiscordId(string $discordId): Channel;

    abstract public function findByGuild(Guild $guild): Collection;

    abstract public function getAll(): Collection;

    abstract public function save(Channel $channel): bool;

    abstract public function remove(Channel $channel): bool;
}