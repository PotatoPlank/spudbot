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
use Spudbot\Interface\IChannelRepository;
use Spudbot\Model\Channel;
use Spudbot\Model\Guild;
use Spudbot\Traits\UsesDoctrine;

class ChannelRepository extends IChannelRepository
{
    use UsesDoctrine;

    private array $fields = [
        'c.id as c_id',
        'c.discord_id as c_discord_id',
        'c.guild_id as c_guild_id',
        'c.created_at as c_created_at',
        'c.modified_at as c_modified_at',
        'g.id as g_id',
        'g.discord_id as g_discord_id',
        'g.output_channel_id as g_output_channel_id',
        'g.output_thread_id as g_output_thread_id',
        'g.created_at as g_created_at',
        'g.modified_at as g_modified_at',
    ];

    public function findById(string|int $id): Channel
    {
        $response = $this->dbal->createQueryBuilder()
            ->select(...$this->fields)
            ->from('channels', 'c')
            ->innerJoin('c', 'guilds', 'g', 'c.guild_id = g.id')
            ->where('c.id = ?')->setParameters([$id])
            ->fetchAssociative();

        if (!$response) {
            throw new OutOfBoundsException("Channel with id {$id} does not exist.");
        }

        return Channel::withDatabaseRow($response);
    }

    public function findByGuild(Guild $guild): Collection
    {
        $collection = new Collection();
        $queryBuilder = $this->dbal->createQueryBuilder();

        $response = $queryBuilder->select(...$this->fields)
            ->from('channels', 'c')
            ->innerJoin('c', 'guilds', 'g', 'c.guild_id = g.id')
            ->where('c.guild_id = ?')->setParameter(0, $guild->getId())
            ->fetchAllAssociative();

        if (!empty($response)) {
            foreach ($response as $row) {
                $channel = Channel::withDatabaseRow($row);

                $collection->push($channel);
            }
        }

        return $collection;
    }

    public function getAll(): Collection
    {
        $collection = new Collection();
        $queryBuilder = $this->dbal->createQueryBuilder();

        $response = $queryBuilder->select(...$this->fields)
            ->from('channels', 'c')
            ->innerJoin('c', 'guilds', 'g', 'c.guild_id = g.id')
            ->fetchAllAssociative();

        if (!empty($response)) {
            foreach ($response as $row) {
                $channel = Channel::withDatabaseRow($row);

                $collection->push($channel);
            }
        }

        return $collection;
    }

    public function save(Channel $channel): bool
    {
        $channel->setModifiedAt(Carbon::now());

        if (!$channel->getId()) {
            $channel->setCreatedAt(Carbon::now());

            $columns = [
                'discord_id' => '?',
                'guild_id' => '?',
                'created_at' => '?',
                'modified_at' => '?',
            ];

            $parameters = [
                $channel->getDiscordId(),
                $channel->getGuild()->getId(),
                $channel->getCreatedAt()->toDateTimeString(),
                $channel->getModifiedAt()->toDateTimeString(),
            ];

            $impactedRows = $this->dbal->createQueryBuilder()
                ->insert('channels')
                ->values($columns)->setParameters($parameters)
                ->executeStatement();
            $channel->setId($this->dbal->lastInsertId());

            return $impactedRows > 0;
        }

        $parameters = [
            $channel->getGuild()->getId(),
            $channel->getDiscordId(),
            $channel->getModifiedAt()->toDateTimeString(),
            $channel->getId(),
        ];

        $impactedRows = $this->dbal->createQueryBuilder()
            ->update('channels')
            ->set('guild_id', '?')
            ->set('discord_id', '?')
            ->set('modified_at', '?')
            ->where('id = ?')
            ->setParameters($parameters)
            ->executeStatement();

        return $impactedRows > 0;
    }

    public function remove(Channel $channel): bool
    {
        if (!$channel->getId()) {
            throw new OutOfBoundsException("Channel is unable to be removed without a proper id.");
        }

        $impactedRows = $this->dbal->createQueryBuilder()
            ->delete('channels')
            ->where('id = ?')
            ->setParameter(0, $channel->getId())
            ->executeStatement();
        if ($impactedRows === 0) {
            throw new \RuntimeException("Removing channel #{$channel->getId()} was unsuccessful");
        }

        return true;
    }

    public function findByPart(\Discord\Parts\Channel\Channel $channel): Channel
    {
        return $this->findByDiscordId($channel->id, $channel->guild->id);
    }

    public function findByDiscordId(string $discordId, string $discordGuildId): Channel
    {
        $response = $this->dbal->createQueryBuilder()
            ->select(...$this->fields)
            ->from('channels', 'c')
            ->innerJoin('c', 'guilds', 'g', 'c.guild_id = g.id')
            ->where('c.discord_id = ?')->andWhere('g.discord_id = ?')
            ->setParameters([$discordId, $discordGuildId])
            ->fetchAssociative();

        if (!$response) {
            throw new OutOfBoundsException("Channel with discord id {$discordId} does not exist.");
        }

        return Channel::withDatabaseRow($response);
    }
}