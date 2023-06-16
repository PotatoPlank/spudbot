<?php
declare(strict_types=1);

namespace Spudbot\Repository\SQL;

use Carbon\Carbon;
use Discord\Parts\Part;
use Doctrine\DBAL\Cache\QueryCacheProfile;
use OutOfBoundsException;
use Spudbot\Collection;
use Spudbot\Interface\IMemberRepository;
use Spudbot\Model;
use Spudbot\Model\Member;
use Spudbot\Repository\SQLRepository;
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

    public function findByPart(\Discord\Parts\Thread\Member|\Discord\Parts\User\Member|Part $part): Member
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
        // TODO: Implement findByDiscordId() method.
    }

    public function findByGuild(Model\Guild $guild): Collection
    {
        // TODO: Implement findByDiscordId() method.
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

    public function save(Member|Model $model): bool
    {
        // TODO: Implement save() method.
    }

    public function remove(Member|Model $model): bool
    {
        // TODO: Implement remove() method.
    }

    public function saveEventAttendance(Member $member): bool
    {
        // TODO: Implement saveEventAttendance() method.
    }
}