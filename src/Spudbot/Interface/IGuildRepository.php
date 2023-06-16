<?php

namespace Spudbot\Interface;

use Spudbot\Collection;
use Spudbot\Model\Guild;

abstract class IGuildRepository
{
    abstract public function findById(string|int $id): Guild;
    abstract public function findByDiscordId(string $discordId): Guild;
    abstract public function findByPart(\Discord\Parts\Guild\Guild $part): Guild;
    abstract public function getAll(): Collection;

    abstract public function save(Guild $guild): bool;
    abstract public function remove(Guild $guild): bool;
}