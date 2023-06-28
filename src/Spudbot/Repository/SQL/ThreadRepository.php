<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

declare(strict_types=1);

namespace Spudbot\Repository\SQL;

use Carbon\Carbon;
use OutOfBoundsException;
use Spudbot\Helpers\Collection;
use Spudbot\Interface\IThreadRepository;
use Spudbot\Model\Guild;
use Spudbot\Model\Thread;
use Spudbot\Traits\UsesDoctrine;

class ThreadRepository extends IThreadRepository
{
    use UsesDoctrine;

    private array $fields = [
        't.id as t_id',
        't.discord_id as t_discord_id',
        't.guild_id as t_guild_id',
        't.created_at as t_created_at',
        't.modified_at as t_modified_at',
        'g.id as g_id',
        'g.discord_id as g_discord_id',
        'g.output_channel_id as g_output_channel_id',
        'g.output_thread_id as g_output_thread_id',
        'g.created_at as g_created_at',
        'g.modified_at as g_modified_at',
    ];

    public function findById(string|int $id): Thread
    {
        $queryBuilder = $this->dbal->createQueryBuilder();
        $response = $queryBuilder->select(...$this->fields)->from('threads', 't')
            ->innerJoin('t', 'guilds', 'g', 't.guild_id = g.id')
            ->where('id = ?')->setParameters([$id])
            ->fetchAssociative();

        if (!$response) {
            throw new OutOfBoundsException("Thread with id {$id} does not exist.");
        }

        return Thread::withDatabaseRow($response);
    }

    public function findByPart(\Discord\Parts\Thread\Thread $thread): Thread
    {
        return $this->findByDiscordId($thread->id, $thread->guild->id);
    }

    public function findByDiscordId(string $discordId, string $discordGuildId): Thread
    {
        $queryBuilder = $this->dbal->createQueryBuilder();
        $response = $queryBuilder->select(...$this->fields)->from('threads', 't')
            ->innerJoin('t', 'guilds', 'g', 't.guild_id = g.id')
            ->where('t.discord_id = ?')->andWhere('g.discord_id = ?')
            ->setParameters([$discordId, $discordGuildId])
            ->fetchAssociative();

        if (!$response) {
            throw new OutOfBoundsException("Thread with id {$discordId} does not exist.");
        }

        return Thread::withDatabaseRow($response);
    }

    public function findByGuild(Guild $guild): Collection
    {
        $collection = new Collection();
        $queryBuilder = $this->dbal->createQueryBuilder();

        $response = $queryBuilder->select(...$this->fields)->from('threads', 't')
            ->innerJoin('t', 'guilds', 'g', 't.guild_id = g.id')
            ->where('t.guild_id = ?')->setParameters([$guild->getId()])
            ->fetchAllAssociative();

        if (!empty($response)) {
            foreach ($response as $row) {
                $thread = Thread::withDatabaseRow($row);

                $collection->push($thread);
            }
        }

        return $collection;
    }

    public function getAll(): Collection
    {
        $collection = new Collection();
        $guild = new GuildRepository($this->dbal);
        $queryBuilder = $this->dbal->createQueryBuilder();

        $response = $queryBuilder->select(...$this->fields)
            ->from('threads', 't')
            ->innerJoin('t', 'guilds', 'g', 't.guild_id = g.id')
            ->fetchAllAssociative();

        if (!empty($response)) {
            foreach ($response as $row) {
                $thread = Thread::withDatabaseRow($row);

                $collection->push($thread);
            }
        }

        return $collection;
    }

    public function save(Thread $thread): bool
    {
        $thread->setModifiedAt(Carbon::now());

        if (!$thread->getId()) {
            $thread->setCreatedAt(Carbon::now());

            $columns = [
                'discord_id' => '?',
                'guild_id' => '?',
                'created_at' => '?',
                'modified_at' => '?',
            ];

            $parameters = [
                $thread->getDiscordId(),
                $thread->getGuild()->getId(),
                $thread->getCreatedAt()->toDateTimeString(),
                $thread->getModifiedAt()->toDateTimeString(),
            ];

            $impactedRows = $this->dbal->createQueryBuilder()
                ->insert('threads')->values($columns)->setParameters($parameters)
                ->executeStatement();
            $thread->setId($this->dbal->lastInsertId());

            return $impactedRows > 0;
        }

        $parameters = [
            $thread->getModifiedAt()->toDateTimeString(),
            $thread->getId(),
        ];

        $impactedRows = $this->dbal->createQueryBuilder()
            ->update('threads')
            ->set('modified_at', '?')
            ->where('id = ?')
            ->setParameters($parameters)
            ->executeStatement();

        return $impactedRows > 0;
    }

    public function remove(Thread $thread): bool
    {
        if (!$thread->getId()) {
            throw new OutOfBoundsException("Thread is unable to be removed without a proper id.");
        }

        $impactedRows = $this->dbal->createQueryBuilder()
            ->delete('threads')->where('id = ?')->setParameter(0, $thread->getId())
            ->executeStatement();

        if ($impactedRows === 0) {
            throw new \RuntimeException("Removing thread #{$thread->getId()} was unsuccessful");
        }

        return true;
    }
}