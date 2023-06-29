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
use Spudbot\Interface\IMemberRepository;
use Spudbot\Model\EventAttendance;
use Spudbot\Model\Guild;
use Spudbot\Model\Member;
use Spudbot\Traits\UsesDoctrine;

class MemberRepository extends IMemberRepository
{
    use UsesDoctrine;

    private array $fields = [
        'm.id as m_id',
        'm.discord_id as m_discord_id',
        'm.total_comments as m_total_comments',
        'm.username as m_username',
        'm.verified_by as m_verified_by',
        'm.created_at as m_created_at',
        'm.modified_at as m_modified_at',
        'g.id as g_id',
        'g.discord_id as g_discord_id',
        'g.output_channel_id as g_output_channel_id',
        'g.output_thread_id as g_output_thread_id',
        'g.created_at as g_created_at',
        'g.modified_at as g_modified_at',
    ];

    public function findById(string|int $id): Member
    {
        $response = $this->dbal->createQueryBuilder()
            ->select(...$this->fields)
            ->from('members', 'm')
            ->innerJoin('m', 'guilds', 'g', 'm.guild_id = g.id')
            ->where('m.id = ?')->setParameters([$id])
            ->fetchAssociative();

        if (!$response) {
            throw new OutOfBoundsException("Member with id {$id} does not exist.");
        }

        return Member::withDatabaseRow($response);
    }

    public function findByPart(\Discord\Parts\User\Member $member): Member
    {
        return $this->findByDiscordId($member->id, $member->guild->id);
    }

    public function findByDiscordId(string $discordId, string $discordGuildId): Member
    {
        $queryBuilder = $this->dbal->createQueryBuilder();
        $response = $queryBuilder->select(...$this->fields)->from('members', 'm')
            ->innerJoin('m', 'guilds', 'g', 'm.guild_id = g.id')
            ->where('m.discord_id = ?')->andWhere('g.discord_id = ?')
            ->setParameters([$discordId, $discordGuildId])
            ->fetchAssociative();

        if (!$response) {
            throw new OutOfBoundsException("Member with id {$discordId} does not exist.");
        }

        $guild = new GuildRepository($this->dbal);

        return Member::withDatabaseRow($response);
    }

    public function findByGuild(Guild $guild): Collection
    {
        $collection = new Collection();
        $queryBuilder = $this->dbal->createQueryBuilder();

        $response = $queryBuilder->select(...$this->fields)
            ->from('members', 'm')
            ->innerJoin('m', 'guilds', 'g', 'm.guild_id = g.id')
            ->where('m.guild_id = ?')->setParameter(0, $guild->getId())
            ->fetchAllAssociative();

        if (!empty($response)) {
            foreach ($response as $row) {
                $member = Member::withDatabaseRow($row);

                $collection->push($member);
            }
        }

        return $collection;
    }

    public function getAll(): Collection
    {
        $collection = new Collection();
        $guild = new GuildRepository($this->dbal);
        $queryBuilder = $this->dbal->createQueryBuilder();

        $response = $queryBuilder->select(...$this->fields)->from('members', 'm')
            ->innerJoin('m', 'guilds', 'g', 'm.guild_id = g.id')
            ->fetchAllAssociative();

        if (!empty($response)) {
            foreach ($response as $row) {
                $member = Member::withDatabaseRow($row);

                $collection->push($member);
            }
        }

        return $collection;
    }

    public function getEventAttendance(Member $member): Collection
    {
        $collection = new Collection();
        $event = new EventRepository($this->dbal);
        $queryBuilder = $this->dbal->createQueryBuilder();
        $fields = $this->fields;
        $fields[] = 'ea.id as ea_id';
        $fields[] = 'ea.event_id as ea_event_id';
        $fields[] = 'ea.member_id as ea_member_id';
        $fields[] = 'ea.status as ea_status';
        $fields[] = 'ea.no_show as ea_no_show';
        $fields[] = 'ea.created_at as ea_created_at';
        $fields[] = 'ea.modified_at as ea_modified_at';
        $fields[] = 'e.id as e_id';
        $fields[] = 'e.guild_id as e_guild_id';
        $fields[] = 'e.channel_id as e_channel_id';
        $fields[] = 'e.name as e_name';
        $fields[] = 'e.type as e_type';
        $fields[] = 'e.sesh_id as e_sesh_id';
        $fields[] = 'e.native_id as e_native_id';
        $fields[] = 'e.scheduled_at as e_scheduled_at';
        $fields[] = 'e.created_at as e_created_at';
        $fields[] = 'e.modified_at as e_modified_at';

        $response = $queryBuilder->select(...$fields)->from('members', 'm')
            ->innerJoin('m', 'guilds', 'g', 'm.guild_id = g.id')
            ->innerJoin('m', 'event_attendance', 'ea', 'm.id = ea.member_id')
            ->innerJoin('ea', 'events', 'e', 'e.id = ea.event_id')
            ->where('m.id = ?')->setParameters([$member->getId()])
            ->fetchAllAssociative();

        if (!empty($response)) {
            foreach ($response as $row) {
                $attendance = EventAttendance::withDatabaseRow($row);

                $collection->push($attendance);
            }
        }

        return $collection;
    }

    public function save(Member $member): bool
    {
        $member->setModifiedAt(Carbon::now());

        if (!$member->getId()) {
            $member->setCreatedAt(Carbon::now());

            $columns = [
                'discord_id' => '?',
                'guild_id' => '?',
                'total_comments' => '?',
                'created_at' => '?',
                'modified_at' => '?',
            ];

            $parameters = [
                $member->getDiscordId(),
                $member->getGuild()->getId(),
                $member->getTotalComments(),
                $member->getCreatedAt()->toDateTimeString(),
                $member->getModifiedAt()->toDateTimeString(),
            ];

            $impactedRows = $this->dbal->createQueryBuilder()
                ->insert('members')->values($columns)->setParameters($parameters)
                ->executeStatement();
            $member->setId($this->dbal->lastInsertId());

            return $impactedRows > 0;
        }

        $parameters = [
            $member->getTotalComments(),
            $member->getModifiedAt()->toDateTimeString(),
            $member->getId(),
        ];

        $impactedRows = $this->dbal->createQueryBuilder()
            ->update('members')
            ->set('total_comments', '?')
            ->set('modified_at', '?')
            ->where('id = ?')
            ->setParameters($parameters)
            ->executeStatement();

        return $impactedRows > 0;
    }

    public function remove(Member $member): bool
    {
        if (!$member->getId()) {
            throw new OutOfBoundsException("Member is unable to be removed without a proper id.");
        }

        $impactedRows = $this->dbal->createQueryBuilder()
            ->delete('members')->where('id = ?')->setParameter(0, $member->getId())
            ->executeStatement();
        if ($impactedRows === 0) {
            throw new \RuntimeException("Removing member #{$member->getId()} was unsuccessful");
        }

        $this->dbal->createQueryBuilder()
            ->delete('event_attendance')->where('member_id = ?')->setParameter(0, $member->getId())
            ->executeStatement();

        return true;
    }

    public function saveMemberEventAttendance(EventAttendance $eventAttendance): bool
    {
        $eventAttendance->setModifiedAt(Carbon::now());

        if (!$eventAttendance->getId()) {
            $eventAttendance->setCreatedAt(Carbon::now());

            $columns = [
                'event_id' => '?',
                'member_id' => '?',
                'status' => '?',
                'no_show' => '?',
                'created_at' => '?',
                'modified_at' => '?',
            ];

            $parameters = [
                $eventAttendance->getEvent()->getId(),
                $eventAttendance->getMember()->getId(),
                $eventAttendance->getStatus(),
                $eventAttendance->getNoShowStatus() ? 1 : 0,
                $eventAttendance->getCreatedAt()->toDateTimeString(),
                $eventAttendance->getModifiedAt()->toDateTimeString(),
            ];

            $impactedRows = $this->dbal->createQueryBuilder()
                ->insert('event_attendance')->values($columns)->setParameters($parameters)
                ->executeStatement();
            $eventAttendance->setId($this->dbal->lastInsertId());

            return $impactedRows > 0;
        }

        $parameters = [
            $eventAttendance->getStatus(),
            $eventAttendance->getNoShowStatus() ? 1 : 0,
            $eventAttendance->getModifiedAt()->toDateTimeString(),
            $eventAttendance->getId(),
        ];

        $impactedRows = $this->dbal->createQueryBuilder()
            ->update('event_attendance')
            ->set('status', '?')
            ->set('no_show', '?')
            ->set('modified_at', '?')
            ->where('id = ?')
            ->setParameters($parameters)
            ->executeStatement();

        return $impactedRows > 0;
    }
}