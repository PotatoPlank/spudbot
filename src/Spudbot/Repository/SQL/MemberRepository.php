<?php
declare(strict_types=1);

namespace Spudbot\Repository\SQL;

use Carbon\Carbon;
use Discord\Parts\Part;
use OutOfBoundsException;
use Spudbot\Collection;
use Spudbot\Model;
use Spudbot\Model\Guild;
use Spudbot\Model\Member;
use Spudbot\Repository\SQLRepository;

class MemberRepository extends SQLRepository
{

    public function findById(string $id): Model
    {
        $queryBuilder = $this->dbal->createQueryBuilder();
        $response = $queryBuilder->select('*')->from('members')
            ->where('id = ?')->setParameters([$id])
            ->fetchAssociative();

        if(!$response){
            throw new OutOfBoundsException("Guild with id {$id} does not exist.");
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

    public function findByPart(\Discord\Parts\Thread\Member|\Discord\Parts\User\Member|Part $part): Model
    {
        $queryBuilder = $this->dbal->createQueryBuilder();
        $response = $queryBuilder->select('*')->from('members')
            ->where('id = ?')->setParameters([$part->id])
            ->fetchAssociative();

        if(!$response){
            throw new OutOfBoundsException("Guild with id {$part->id} does not exist.");
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

    public function save(Member|Model $model): bool
    {
        // TODO: Implement save() method.
    }

    public function remove(Member|Model $model): bool
    {
        // TODO: Implement remove() method.
    }
}