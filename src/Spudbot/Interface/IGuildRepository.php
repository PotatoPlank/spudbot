<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023-2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

declare(strict_types=1);

namespace Spudbot\Interface;

use Spudbot\Helpers\Collection;
use Spudbot\Model\Guild;

abstract class IGuildRepository
{
    abstract public function findById(string|int $id): Guild;

    abstract public function findByDiscordId(string $discordId): Guild;

    abstract public function findByPart(\Discord\Parts\Guild\Guild $guild): Guild;

    abstract public function getAll(): Collection;

    abstract public function save(Guild $guild): Guild;

    abstract public function remove(Guild $guild): bool;
}
