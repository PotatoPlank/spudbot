<?php

namespace Spudbot\Interface;

use Spudbot\Collection;
use Spudbot\Model\Guild;
use Spudbot\Model\Thread;

abstract class IThreadRepository
{
    abstract public function findById(string|int $id): Thread;
    abstract public function findByDiscordId(string $discordId): Thread;
    abstract public function findByPart(\Discord\Parts\Thread\Thread $part): Thread;
    abstract public function getAll(): Collection;

    abstract public function save(Thread $thread): bool;
    abstract public function remove(Thread $thread): bool;
}