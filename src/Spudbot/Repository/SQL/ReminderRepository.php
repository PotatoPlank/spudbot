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
use Spudbot\Interface\IReminderRepository;
use Spudbot\Model\Channel;
use Spudbot\Model\Guild;
use Spudbot\Model\Reminder;
use Spudbot\Traits\UsesDoctrine;

class ReminderRepository extends IReminderRepository
{
    use UsesDoctrine;

    private array $fields = [
        'r.id as r_id',
        'r.description as r_description',
        'r.mention_role as r_mention_role',
        'r.scheduled_at as r_scheduled_at',
        'r.repeats as r_repeats',
        'r.channel_id as r_channel_id',
        'r.guild_id as r_guild_id',
        'r.created_at as r_created_at',
        'r.modified_at as r_modified_at',
        'g.id as g_id',
        'g.discord_id as g_discord_id',
        'g.output_channel_id as g_output_channel_id',
        'g.output_thread_id as g_output_thread_id',
        'g.created_at as g_created_at',
        'g.modified_at as g_modified_at',
        'c.id as c_id',
        'c.discord_id as c_discord_id',
        'c.guild_id as c_guild_id',
        'c.created_at as c_created_at',
        'c.modified_at as c_modified_at',
    ];

    public function findById(string|int $id): Reminder
    {
        $response = $this->dbal->createQueryBuilder()
            ->select(...$this->fields)
            ->from('reminders', 'r')
            ->innerJoin('r', 'guilds', 'g', 'r.guild_id = g.id')
            ->innerJoin('r', 'channels', 'c', 'r.channel_id = c.id')
            ->where('r.id = ?')->setParameters([$id])
            ->fetchAssociative();

        if (!$response) {
            throw new OutOfBoundsException("Reminder with id {$id} does not exist.");
        }

        return Reminder::withDatabaseRow($response);
    }

    public function findByGuild(Guild $guild): Collection
    {
        $collection = new Collection();
        $queryBuilder = $this->dbal->createQueryBuilder();

        $response = $queryBuilder->select(...$this->fields)
            ->from('reminders', 'r')
            ->innerJoin('r', 'guilds', 'g', 'r.guild_id = g.id')
            ->innerJoin('r', 'channels', 'c', 'r.channel_id = c.id')
            ->where('r.guild_id = ?')->setParameter(0, $guild->getId())
            ->fetchAllAssociative();

        if (!empty($response)) {
            foreach ($response as $row) {
                $reminder = Reminder::withDatabaseRow($row);

                $collection->push($reminder);
            }
        }

        return $collection;
    }

    public function getAll(): Collection
    {
        $collection = new Collection();
        $queryBuilder = $this->dbal->createQueryBuilder();

        $response = $queryBuilder->select(...$this->fields)
            ->from('reminders', 'r')
            ->innerJoin('r', 'guilds', 'g', 'r.guild_id = g.id')
            ->innerJoin('r', 'channels', 'c', 'r.channel_id = c.id')
            ->fetchAllAssociative();

        if (!empty($response)) {
            foreach ($response as $row) {
                $reminder = Reminder::withDatabaseRow($row);

                $collection->push($reminder);
            }
        }

        return $collection;
    }

    public function save(Reminder $reminder): bool
    {
        $reminder->setModifiedAt(Carbon::now());

        if (!$reminder->getId()) {
            $reminder->setCreatedAt(Carbon::now());

            $columns = [
                'description' => '?',
                'mention_role' => '?',
                'scheduled_at' => '?',
                'repeats' => '?',
                'channel_id' => '?',
                'guild_id' => '?',
                'created_at' => '?',
                'modified_at' => '?',
            ];

            $parameters = [
                $reminder->getDescription(),
                $reminder->getMentionableRole(),
                $reminder->getScheduledAt()->toDateTimeString(),
                $reminder->getRepeats(),
                $reminder->getChannel()->getId(),
                $reminder->getGuild()->getId(),
                $reminder->getCreatedAt()->toDateTimeString(),
                $reminder->getModifiedAt()->toDateTimeString(),
            ];

            $impactedRows = $this->dbal->createQueryBuilder()
                ->insert('reminders')
                ->values($columns)->setParameters($parameters)
                ->executeStatement();
            $reminder->setId($this->dbal->lastInsertId());

            return $impactedRows > 0;
        }

        $parameters = [
            $reminder->getDescription(),
            $reminder->getMentionableRole(),
            $reminder->getScheduledAt()->toDateTimeString(),
            $reminder->getRepeats(),
            $reminder->getModifiedAt()->toDateTimeString(),
            $reminder->getId(),
        ];

        $impactedRows = $this->dbal->createQueryBuilder()
            ->update('reminders')
            ->set('description', '?')
            ->set('mention_role', '?')
            ->set('scheduled_at', '?')
            ->set('repeats', '?')
            ->set('modified_at', '?')
            ->where('id = ?')
            ->setParameters($parameters)
            ->executeStatement();

        return $impactedRows > 0;
    }

    public function remove(Reminder $reminder): bool
    {
        if (!$reminder->getId()) {
            throw new OutOfBoundsException("Reminder is unable to be removed without a proper id.");
        }

        $impactedRows = $this->dbal->createQueryBuilder()
            ->delete('reminders')
            ->where('id = ?')
            ->setParameter(0, $reminder->getId())
            ->executeStatement();
        if ($impactedRows === 0) {
            throw new \RuntimeException("Removing reminder #{$reminder->getId()} was unsuccessful");
        }

        return true;
    }


    public function findByDate(Carbon $scheduledAt): Collection
    {
        $collection = new Collection();
        $queryBuilder = $this->dbal->createQueryBuilder();
        $scheduledAt->setTimezone('UTC');

        $response = $queryBuilder->select(...$this->fields)
            ->from('reminders', 'r')
            ->innerJoin('r', 'guilds', 'g', 'r.guild_id = g.id')
            ->innerJoin('r', 'channels', 'c', 'r.channel_id = c.id')
            ->where('r.scheduled_at = ?')
            ->setParameter(0, $scheduledAt->toDate())
            ->fetchAllAssociative();

        if (!empty($response)) {
            foreach ($response as $row) {
                $reminder = Reminder::withDatabaseRow($row);

                $collection->push($reminder);
            }
        }

        return $collection;
    }

    public function findByDateTime(Carbon $scheduledAt): Reminder
    {
        $scheduledAt->setTimezone('UTC');
        $response = $this->dbal->createQueryBuilder()
            ->select(...$this->fields)
            ->from('reminders', 'r')
            ->innerJoin('r', 'guilds', 'g', 'r.guild_id = g.id')
            ->innerJoin('r', 'channels', 'c', 'r.channel_id = c.id')
            ->where('r.scheduled_at = ?')
            ->setParameters([$scheduledAt->toDateTimeString()])
            ->fetchAssociative();

        if (!$response) {
            throw new OutOfBoundsException("Reminder at {$scheduledAt->toDateTimeString()} does not exist.");
        }

        return Reminder::withDatabaseRow($response);
    }

    public function findByChannel(Channel $channel): Collection
    {
        $collection = new Collection();
        $queryBuilder = $this->dbal->createQueryBuilder();

        $response = $queryBuilder->select(...$this->fields)
            ->from('reminders', 'r')
            ->innerJoin('r', 'guilds', 'g', 'r.guild_id = g.id')
            ->innerJoin('r', 'channels', 'c', 'r.channel_id = c.id')
            ->where('r.channel_id = ?')
            ->setParameter(0, $channel->getId())
            ->fetchAllAssociative();

        if (!empty($response)) {
            foreach ($response as $row) {
                $reminder = Reminder::withDatabaseRow($row);

                $collection->push($reminder);
            }
        }
        return $collection;
    }
}