<?php
declare(strict_types=1);

namespace Spudbot\Repository\SQL;

use Carbon\Carbon;
use Discord\Parts\Part;
use InvalidArgumentException;
use OutOfBoundsException;
use Spudbot\Collection;
use Spudbot\Interface\IEventRepository;
use Spudbot\Model;
use Spudbot\Model\Guild;
use Spudbot\Model\Member;
use Spudbot\Traits\UsesDoctrine;

class EventRepository extends IEventRepository
{
    use UsesDoctrine;
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

        return Model\Event::withDatabaseRow($response, $guild->findById($response['guild_id']));
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

        return Model\Event::withDatabaseRow($response, $guild->findById($response['guild_id']));
    }

    public function findByDiscordId(string $discordId): Model\Event
    {
        $queryBuilder = $this->dbal->createQueryBuilder();
        $guild = new GuildRepository($this->dbal);

        $response = $queryBuilder->select('*')->from('events')
            ->where('native_id = ?')->setParameters([$discordId])
            ->fetchAssociative();

        if(!$response){
            throw new OutOfBoundsException("Event with id {$discordId} does not exist.");
        }

        return Model\Event::withDatabaseRow($response, $guild->findById($response['guild_id']));
    }

    public function findBySeshId(string $seshId): Model\Event
    {
        $queryBuilder = $this->dbal->createQueryBuilder();
        $guild = new GuildRepository($this->dbal);

        $response = $queryBuilder->select('*')->from('events')
            ->where('sesh_id = ?')->setParameters([$seshId])
            ->fetchAssociative();

        if(!$response){
            throw new OutOfBoundsException("Event with id {$seshId} does not exist.");
        }

        return Model\Event::withDatabaseRow($response, $guild->findById($response['guild_id']));
    }

    public function findByGuild(Guild $guild): Collection
    {
        $collection = new Collection();
        $queryBuilder = $this->dbal->createQueryBuilder();

        $response = $queryBuilder->select('*')->from('events')
            ->where('guild_id = ?')->setParameters([$guild->getId()])
            ->fetchAllAssociative();

        if(!empty($response)){
            foreach ($response as $row) {
                $event = Model\Event::withDatabaseRow($row, $guild);

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

        $response = $queryBuilder->select('*')->from('events')
            ->fetchAllAssociative();

        if(!empty($response)){
            foreach ($response as $row) {
                $event = Model\Event::withDatabaseRow($row, $guild->findById($row['guild_id']));

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
                $attendance = Model\EventAttendance::withDatabaseRow($row, $event, $member->findById($row['member_id']));

                $collection->push($attendance);
            }
        }

        return $collection;
    }

    public function getAttendanceByMemberAndEvent(Member $member, Model\Event $event): Model\EventAttendance
    {
        $queryBuilder = $this->dbal->createQueryBuilder();

        $response = $queryBuilder->select('*')
            ->from('event_attendance')->where('event_id = ?')->andWhere('member_id = ?')
            ->setParameters([$event->getId(), $member->getId()])
            ->fetchAssociative();

        if(!$response){
            throw new OutOfBoundsException("Event data associated with specified user and event does not exist.");
        }

        return Model\EventAttendance::withDatabaseRow($response, $event, $member);
    }

    public function save(Model\Event $event): bool
    {
        $event->setModifiedAt(Carbon::now());

        if(!$event->getId()){
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

    public function remove(Model\Event $event): bool
    {
        if(!$event->getId()){
            Throw New OutOfBoundsException("Event is unable to be removed without a proper id.");
        }

        $impactedRows = $this->dbal->createQueryBuilder()
            ->delete('events')->where('id = ?')->setParameter(0, $event->getId())
            ->executeStatement();
        if($impactedRows === 0){
            Throw New \RuntimeException("Removing event #{$event->getId()} was unsuccessful");
        }

        $this->dbal->createQueryBuilder()
            ->delete('event_attendance')->where('event_id = ?')->setParameter(0, $event->getId())
            ->executeStatement();

        return true;
    }
}