<?php
declare(strict_types=1);

namespace Spudbot\Repository\SQL;

use Carbon\Carbon;
use Discord\Parts\Part;
use InvalidArgumentException;
use OutOfBoundsException;
use Spudbot\Collection;
use Spudbot\Model;
use Spudbot\Model\Guild;
use Spudbot\Model\Member;
use Spudbot\Repository\SQLRepository;
use Spudbot\Type\Event;

class EventRepository extends SQLRepository
{
    public function findById(string|int $id): Model\Event
    {
        $queryBuilder = $this->dbal->createQueryBuilder();
        $guild = new GuildRepository($this->dbal);
        $response = $queryBuilder->select('*')->from('events')
            ->where('id = ?')->setParameters([$id])
            ->fetchAssociative();

        if(!$response){
            throw new OutOfBoundsException("Event with id {$id} does not exist.");
        }

        $event = new Model\Event();
        $event->setId($response['id']);
        $event->setGuild($guild->findById($response['guild_id']));
        $event->setChannelId($response['channel_id']);
        $event->setName($response['name']);
        $event->setType(Event::from($response['type']));
        $event->setSeshId($response['sesh_id']);
        $event->setNativeId($response['native_id']);
        $event->setScheduledAt(Carbon::parse($response['scheduled_at']));
        $event->setCreatedAt(Carbon::parse($response['created_at']));
        $event->setModifiedAt(Carbon::parse($response['modified_at']));

        return $event;
    }

    public function findByPart(\stdClass|Part $part): Model\Event
    {
        if(!isset($part->guild_scheduled_event_id)){
            throw new InvalidArgumentException("Part is not an instance with an Event Id.");
        }
        $queryBuilder = $this->dbal->createQueryBuilder();
        $guild = new GuildRepository($this->dbal);

        $response = $queryBuilder->select('*')->from('events')
            ->where('native_id = ?')
            ->setParameters([$part->guild_scheduled_event_id])
            ->fetchAssociative();

        if(!$response){
            throw new OutOfBoundsException("Event with id {$part->guild_scheduled_event_id} does not exist.");
        }

        $event = new Model\Event();
        $event->setId($response['id']);
        $event->setGuild($guild->findById($response['guild_id']));
        $event->setChannelId($response['channel_id']);
        $event->setName($response['name']);
        $event->setType(Event::from($response['type']));
        $event->setSeshId($response['sesh_id']);
        $event->setNativeId($response['native_id']);
        $event->setScheduledAt(Carbon::parse($response['scheduled_at']));
        $event->setCreatedAt(Carbon::parse($response['created_at']));
        $event->setModifiedAt(Carbon::parse($response['modified_at']));

        return $event;
    }

    public function getAll(): Collection
    {
        $collection = new Collection();
        $guild = new GuildRepository($this->dbal);
        $queryBuilder = $this->dbal->createQueryBuilder();

        $response = $queryBuilder->select('*')->from('events')
            ->fetchAllAssociative();

        if(!empty($response)){
            foreach ($response as $row) {
                $event = new Model\Event();
                $event->setId($row['id']);
                $event->setGuild($guild->findById($row['guild_id']));
                $event->setChannelId($row['channel_id']);
                $event->setName($row['name']);
                $event->setType(Event::from($row['type']));
                $event->setSeshId($row['sesh_id']);
                $event->setNativeId($row['native_id']);
                $event->setScheduledAt(Carbon::parse($row['scheduled_at']));
                $event->setCreatedAt(Carbon::parse($row['created_at']));
                $event->setModifiedAt(Carbon::parse($row['modified_at']));

                $collection->push($event);
            }
        }

        return $collection;
    }

    public function getAttendanceByEvent(Model\Event $event): Collection
    {
        $collection = new Collection();
        $member = new MemberRepository($this->dbal);
        $queryBuilder = $this->dbal->createQueryBuilder();

        $response = $queryBuilder->select('*')->from('event_attendance')
            ->where('event_id = ?')->setParameters([$event->getId()])
            ->fetchAllAssociative();

        if(!empty($response)){
            foreach ($response as $row) {
                $attendance = new Model\EventAttendance();
                $attendance->setId($row['id']);
                $attendance->setEvent($event);
                $attendance->setMember($member->findById($row['member_id']));
                $attendance->setStatus($row['status']);
                $attendance->wasNoShow((bool) $row['no_show']);
                $attendance->setCreatedAt(Carbon::parse($row['created_at']));
                $attendance->setModifiedAt(Carbon::parse($row['modified_at']));

                $collection->push($attendance);
            }
        }

        return $collection;
    }

    public function getAttendanceByMemberAndEvent(Member $member, Model\Event $event): Collection
    {
        $collection = new Collection();
        $queryBuilder = $this->dbal->createQueryBuilder();

        $response = $queryBuilder->select('*')->from('event_attendance')
            ->where('event_id = ?')->andWhere('member_id = ?')
            ->setParameters([$event->getId(), $member->getId()])
            ->fetchAllAssociative();

        if(!empty($response)){
            foreach ($response as $row) {
                $attendance = new Model\EventAttendance();
                $attendance->setId($row['id']);
                $attendance->setEvent($event);
                $attendance->setMember($member);
                $attendance->setStatus($row['status']);
                $attendance->wasNoShow((bool) $row['no_show']);
                $attendance->setCreatedAt(Carbon::parse($row['created_at']));
                $attendance->setModifiedAt(Carbon::parse($row['modified_at']));

                $collection->push($attendance);
            }
        }

        return $collection;
    }

    public function save(Guild|Model $model): bool
    {
        $model->setModifiedAt(Carbon::now());
        // TODO: implemented the save() method.
    }

    public function remove(Guild|Model $model): bool
    {
        // TODO: Implement remove() method.
    }
}