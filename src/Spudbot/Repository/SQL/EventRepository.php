<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

declare(strict_types=1);

namespace Spudbot\Repository\SQL;

use Carbon\Carbon;
use Discord\Parts\Guild\ScheduledEvent;
use InvalidArgumentException;
use OutOfBoundsException;
use Spudbot\Helpers\Collection;
use Spudbot\Interface\IEventRepository;
use Spudbot\Model\Event;
use Spudbot\Model\EventAttendance;
use Spudbot\Model\Guild;
use Spudbot\Model\Member;
use Spudbot\Traits\UsesDoctrine;

class EventRepository extends IEventRepository
{
    use UsesDoctrine;

    private array $fields = [
        'e.id as e_id',
        'e.guild_id as e_guild_id',
        'e.channel_id as e_channel_id',
        'e.name as e_name',
        'e.type as e_type',
        'e.sesh_id as e_sesh_id',
        'e.native_id as e_native_id',
        'e.scheduled_at as e_scheduled_at',
        'e.created_at as e_created_at',
        'e.modified_at as e_modified_at',
        'g.id as g_id',
        'g.discord_id as g_discord_id',
        'g.output_channel_id as g_output_channel_id',
        'g.output_thread_id as g_output_thread_id',
        'g.created_at as g_created_at',
        'g.modified_at as g_modified_at',
    ];

    public function findById(string|int $id): Event
    {
        $queryBuilder = $this->dbal->createQueryBuilder();
        $guild = new GuildRepository($this->dbal);

        $response = $queryBuilder->select(...$this->fields)->from('events', 'e')
            ->innerJoin('e', 'guilds', 'g', 'g.id = e.guild_id')
            ->where('e.id = ?')->setParameters([$id])
            ->fetchAssociative();

        if (!$response) {
            throw new OutOfBoundsException("Event with id {$id} does not exist.");
        }

        return Event::withDatabaseRow($response);
    }

    public function findByPart(\stdClass|ScheduledEvent $event): Event
    {
        if (!($event instanceof ScheduledEvent) && !isset($event->guild_scheduled_event_id)) {
            throw new InvalidArgumentException("Part is not an instance with an Event Id.");
        }

        $id = $event instanceof ScheduledEvent ? $event->id : $event->guild_scheduled_event_id;

        return $this->findByDiscordId($id, $event->guild_id);
    }

    public function findByDiscordId(string $discordId, string $discordGuildId): Event
    {
        $queryBuilder = $this->dbal->createQueryBuilder();

        $response = $queryBuilder->select(...$this->fields)->from('events', 'e')
            ->innerJoin('e', 'guilds', 'g', 'g.id = e.guild_id')
            ->where('e.native_id = ?')->andWhere('g.discord_id = ?')
            ->setParameters([$discordId, $discordGuildId])
            ->fetchAssociative();

        if (!$response) {
            throw new OutOfBoundsException("Event with id {$discordId} does not exist.");
        }

        return Event::withDatabaseRow($response);
    }

    public function findBySeshId(string $seshId): Event
    {
        $queryBuilder = $this->dbal->createQueryBuilder();

        $response = $queryBuilder->select(...$this->fields)->from('events', 'e')
            ->innerJoin('e', 'guilds', 'g', 'g.id = e.guild_id')
            ->where('e.sesh_id = ?')->setParameters([$seshId])
            ->fetchAssociative();

        if (!$response) {
            throw new OutOfBoundsException("Event with id {$seshId} does not exist.");
        }

        return Event::withDatabaseRow($response);
    }

    public function findByGuild(Guild $guild): Collection
    {
        $collection = new Collection();
        $queryBuilder = $this->dbal->createQueryBuilder();

        $response = $queryBuilder->select(...$this->fields)->from('events', 'e')
            ->innerJoin('e', 'guilds', 'g', 'g.id = e.guild_id')
            ->where('e.guild_id = ?')->setParameters([$guild->getId()])
            ->fetchAllAssociative();

        if (!empty($response)) {
            foreach ($response as $row) {
                $event = Event::withDatabaseRow($row);

                $collection->push($event);
            }
        }

        return $collection;
    }

    public function getAll(): Collection
    {
        $collection = new Collection();
        $guild = new GuildRepository($this->dbal);
        $queryBuilder = $this->dbal->createQueryBuilder();

        $response = $queryBuilder->select(...$this->fields)->from('events', 'e')
            ->innerJoin('e', 'guilds', 'g', 'g.id = e.guild_id')
            ->fetchAllAssociative();

        if (!empty($response)) {
            foreach ($response as $row) {
                $event = Event::withDatabaseRow($row);

                $collection->push($event);
            }
        }

        return $collection;
    }

    public function getAttendanceByEvent(Event $event): Collection
    {
        $collection = new Collection();
        $member = new MemberRepository($this->dbal);
        $queryBuilder = $this->dbal->createQueryBuilder();

        $fields = $this->fields;
        $fields[] = 'ea.id as ea_id';
        $fields[] = 'ea.event_id as ea_event_id';
        $fields[] = 'ea.member_id as ea_member_id';
        $fields[] = 'ea.status as ea_status';
        $fields[] = 'ea.no_show as ea_no_show';
        $fields[] = 'ea.created_at as ea_created_at';
        $fields[] = 'ea.modified_at as ea_modified_at';
        $fields[] = 'm.id as m_id';
        $fields[] = 'm.discord_id as m_discord_id';
        $fields[] = 'm.total_comments as m_total_comments';
        $fields[] = 'm.created_at as m_created_at';
        $fields[] = 'm.modified_at as m_modified_at';

        $response = $queryBuilder->select(...$fields)->from('event_attendance', 'ea')
            ->innerJoin('ea', 'members', 'm', 'm.id = ea.member_id')
            ->innerJoin('m', 'guilds', 'g', 'm.guild_id = g.id')
            ->innerJoin('ea', 'events', 'e', 'e.id = ea.event_id')
            ->where('ea.event_id = ?')->setParameters([$event->getId()])
            ->fetchAllAssociative();

        if (!empty($response)) {
            foreach ($response as $row) {
                $attendance = EventAttendance::withDatabaseRow($row);

                $collection->push($attendance);
            }
        }

        return $collection;
    }

    public function getAttendanceByMemberAndEvent(Member $member, Event $event): EventAttendance
    {
        $queryBuilder = $this->dbal->createQueryBuilder();
        $fields = $this->fields;
        $fields[] = 'ea.id as ea_id';
        $fields[] = 'ea.event_id as ea_event_id';
        $fields[] = 'ea.member_id as ea_member_id';
        $fields[] = 'ea.status as ea_status';
        $fields[] = 'ea.no_show as ea_no_show';
        $fields[] = 'ea.created_at as ea_created_at';
        $fields[] = 'ea.modified_at as ea_modified_at';
        $fields[] = 'm.id as m_id';
        $fields[] = 'm.discord_id as m_discord_id';
        $fields[] = 'm.total_comments as m_total_comments';
        $fields[] = 'm.created_at as m_created_at';
        $fields[] = 'm.modified_at as m_modified_at';

        $response = $queryBuilder->select(...$fields)
            ->from('event_attendance', 'ea')
            ->innerJoin('ea', 'members', 'm', 'm.id = ea.member_id')
            ->innerJoin('m', 'guilds', 'g', 'm.guild_id = g.id')
            ->innerJoin('ea', 'events', 'e', 'e.id = ea.event_id')
            ->where('ea.event_id = ?')->andWhere('ea.member_id = ?')
            ->setParameters([$event->getId(), $member->getId()])
            ->fetchAssociative();

        if (!$response) {
            throw new OutOfBoundsException("Event data associated with specified user and event does not exist.");
        }

        return EventAttendance::withDatabaseRow($response);
    }

    public function save(Event $event): bool
    {
        $event->setModifiedAt(Carbon::now());

        if (!$event->getId()) {
            $event->setCreatedAt(Carbon::now());

            $columns = [
                'guild_id' => '?',
                'channel_id' => '?',
                'name' => '?',
                'type' => '?',
                'sesh_id' => '?',
                'native_id' => '?',
                'scheduled_at' => '?',
                'created_at' => '?',
                'modified_at' => '?',
            ];

            $parameters = [
                $event->getGuild()->getId(),
                $event->getChannelId(),
                $event->getName(),
                $event->getType()->value,
                $event->getSeshId(),
                $event->getNativeId(),
                $event->getScheduledAt(),
                $event->getCreatedAt()->toDateTimeString(),
                $event->getModifiedAt()->toDateTimeString(),
            ];

            $impactedRows = $this->dbal->createQueryBuilder()
                ->insert('events')->values($columns)->setParameters($parameters)
                ->executeStatement();
            $event->setId($this->dbal->lastInsertId());

            return $impactedRows > 0;
        }

        $parameters = [
            $event->getChannelId(),
            $event->getName(),
            $event->getType()->value,
            $event->getSeshId(),
            $event->getNativeId(),
            $event->getScheduledAt(),
            $event->getModifiedAt()->toDateTimeString(),
            $event->getId(),
        ];

        $impactedRows = $this->dbal->createQueryBuilder()
            ->update('events')
            ->set('channel_id', '?')
            ->set('name', '?')
            ->set('type', '?')
            ->set('sesh_id', '?')
            ->set('native_id', '?')
            ->set('scheduled_at', '?')
            ->set('modified_at', '?')
            ->where('id = ?')
            ->setParameters($parameters)
            ->executeStatement();

        return $impactedRows > 0;
    }

    public function remove(Event $event): bool
    {
        if (!$event->getId()) {
            throw new OutOfBoundsException("Event is unable to be removed without a proper id.");
        }

        $impactedRows = $this->dbal->createQueryBuilder()
            ->delete('events')->where('id = ?')->setParameter(0, $event->getId())
            ->executeStatement();
        if ($impactedRows === 0) {
            throw new \RuntimeException("Removing event #{$event->getId()} was unsuccessful");
        }

        $this->dbal->createQueryBuilder()
            ->delete('event_attendance')->where('event_id = ?')->setParameter(0, $event->getId())
            ->executeStatement();

        return true;
    }
}