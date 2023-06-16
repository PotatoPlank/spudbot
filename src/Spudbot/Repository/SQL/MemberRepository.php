<?php
declare(strict_types=1);

namespace Spudbot\Repository\SQL;

use Carbon\Carbon;
use Doctrine\DBAL\Cache\QueryCacheProfile;
use OutOfBoundsException;
use Spudbot\Collection;
use Spudbot\Interface\IMemberRepository;
use Spudbot\Model;
use Spudbot\Model\Member;
use Spudbot\Traits\UsesDoctrine;

class MemberRepository extends IMemberRepository
{
    use UsesDoctrine;
    public function findById(string|int $id): Member
    {
        $queryBuilder = $this->dbal->createQueryBuilder();
        $response = $queryBuilder->select('*')->from('members')
            ->where('id = ?')->setParameters([$id])
            ->enableResultCache(new QueryCacheProfile('10', "member_{$id}"))
            ->fetchAssociative();

        if(!$response){
            throw new OutOfBoundsException("Member with id {$id} does not exist.");
        }

        $guild = new GuildRepository($this->dbal);

        return Member::withDatabaseRow($response, $guild->findById($response['guild_id']));
    }

    public function findByPart(\Discord\Parts\User\Member $part): Member
    {
        $queryBuilder = $this->dbal->createQueryBuilder();
        $response = $queryBuilder->select('*')->from('members')
            ->where('discord_id = ?')->setParameters([$part->id])
            ->enableResultCache(new QueryCacheProfile('10', "member_{$part->id}"))
            ->fetchAssociative();

        if(!$response){
            throw new OutOfBoundsException("Member with id {$part->id} does not exist.");
        }

        $guild = new GuildRepository($this->dbal);

        return Member::withDatabaseRow($response, $guild->findById($response['guild_id']));
    }

    public function findByDiscordId(string $discordId): Member
    {
        $queryBuilder = $this->dbal->createQueryBuilder();
        $response = $queryBuilder->select('*')->from('members')
            ->where('discord_id = ?')->setParameters([$discordId])
            ->enableResultCache(new QueryCacheProfile('10', "member_{$discordId}"))
            ->fetchAssociative();

        if(!$response){
            throw new OutOfBoundsException("Member with id {$discordId} does not exist.");
        }

        $guild = new GuildRepository($this->dbal);

        return Member::withDatabaseRow($response, $guild->findById($response['guild_id']));
    }

    public function findByGuild(Model\Guild $guild): Collection
    {
        $collection = new Collection();
        $queryBuilder = $this->dbal->createQueryBuilder();

        $response = $queryBuilder->select('*')->from('members')
            ->enableResultCache(new QueryCacheProfile('10', "member_list"))
            ->where('guild_id = ?')->setParameter(0, $guild->getId())
            ->fetchAllAssociative();

        if(!empty($response)){
            foreach ($response as $row) {
                $member = Member::withDatabaseRow($response, $guild);

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

        $response = $queryBuilder->select('*')->from('members')
            ->enableResultCache(new QueryCacheProfile('10', "member_list"))
            ->fetchAllAssociative();

        if(!empty($response)){
            foreach ($response as $row) {
                $member = Member::withDatabaseRow($response, $guild->findById($row['guild_id']));

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

        $response = $queryBuilder->select('*')->from('event_attendance')
            ->where('member_id = ?')->setParameters([$member->getId()])
            ->fetchAllAssociative();

        if(!empty($response)){
            foreach ($response as $row) {
                $attendance = Model\EventAttendance::withDatabaseRow($row, $event->findById($row['event_id']), $member);

                $collection->push($attendance);
            }
        }

        return $collection;
    }

    public function save(Member $member): bool
    {
        $member->setModifiedAt(Carbon::now());

        if(!$member->getId()){
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
        if(!$member->getId()){
            Throw New OutOfBoundsException("Member is unable to be removed without a proper id.");
        }

        $impactedRows = $this->dbal->createQueryBuilder()
            ->delete('members')->where('id = ?')->setParameter(0, $member->getId())
            ->executeStatement();
        if($impactedRows === 0){
            Throw New \RuntimeException("Removing member #{$member->getId()} was unsuccessful");
        }

        $this->dbal->createQueryBuilder()
            ->delete('event_attendance')->where('member_id = ?')->setParameter(0, $member->getId())
            ->executeStatement();

        return true;
    }

    public function saveMemberEventAttendance(Model\EventAttendance $eventAttendance): bool
    {
        $eventAttendance->setModifiedAt(Carbon::now());

        if(!$eventAttendance->getId()){
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