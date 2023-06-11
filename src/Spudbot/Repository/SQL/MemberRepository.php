<?php
declare(strict_types=1);

namespace Spudbot\Repository\SQL;

use Carbon\Carbon;
use Discord\Parts\Part;
use OutOfBoundsException;
use Spudbot\Collection;
use Spudbot\Model;
use Spudbot\Model\Member;
use Spudbot\Repository\SQLRepository;

class MemberRepository extends SQLRepository
{

    public function findById(string|int $id): Member
    {
        $queryBuilder = $this->dbal->createQueryBuilder();
        $response = $queryBuilder->select('*')->from('members')
            ->where('id = ?')->setParameters([$id])
            ->fetchAssociative();

        if(!$response){
            throw new OutOfBoundsException("Member with id {$id} does not exist.");
        }

        $guild = new GuildRepository($this->dbal);

        $member = new Member();
        $member->setId($response['id']);
        $member->setDiscordId($response['discord_id']);
        $member->setGuild($guild->findById($response['guild_id']));
        $member->setTotalComments($response['total_comments']);
        $member->setCreatedAt(Carbon::parse($response['created_at']));
        $member->setModifiedAt(Carbon::parse($response['modified_at']));

        return $member;
    }

    public function findByPart(\Discord\Parts\Thread\Member|\Discord\Parts\User\Member|Part $part): Member
    {
        $queryBuilder = $this->dbal->createQueryBuilder();
        $response = $queryBuilder->select('*')->from('members')
            ->where('id = ?')->setParameters([$part->id])
            ->fetchAssociative();

        if(!$response){
            throw new OutOfBoundsException("Member with id {$part->id} does not exist.");
        }

        $guild = new GuildRepository($this->dbal);

        $member = new Member();
        $member->setId($response['id']);
        $member->setDiscordId($response['discord_id']);
        $member->setGuild($guild->findById($response['guild_id']));
        $member->setTotalComments($response['total_comments']);
        $member->setCreatedAt(Carbon::parse($response['created_at']));
        $member->setModifiedAt(Carbon::parse($response['modified_at']));

        return $member;
    }

    public function getAll(): Collection
    {
        $collection = new Collection();
        $guild = new GuildRepository($this->dbal);
        $queryBuilder = $this->dbal->createQueryBuilder();

        $response = $queryBuilder->select('*')->from('guilds')
            ->fetchAllAssociative();

        if(!empty($response)){
            foreach ($response as $row) {
                $member = new Member();
                $member->setId($row['id']);
                $member->setDiscordId($row['discord_id']);
                $member->setGuild($guild->findById($row['guild_id']));
                $member->setTotalComments($row['total_comments']);
                $member->setCreatedAt(Carbon::parse($row['created_at']));
                $member->setModifiedAt(Carbon::parse($row['modified_at']));

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
                $attendance = new Model\EventAttendance();
                $attendance->setId($row['id']);
                $attendance->setEvent($event->findById($row['event_id']));
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

    public function save(Member|Model $model): bool
    {
        // TODO: Implement save() method.
    }

    public function remove(Member|Model $model): bool
    {
        // TODO: Implement remove() method.
    }
}