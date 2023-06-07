<?php
declare(strict_types=1);

namespace Spudbot\Repository\SQL;

use Carbon\Carbon;
use Discord\Parts\Part;
use OutOfBoundsException;
use Spudbot\Collection;
use Spudbot\Model;
use Spudbot\Model\Thread;
use Spudbot\Repository\SQLRepository;

class ThreadRepository extends SQLRepository
{

    public function findById(string $id): Thread
    {
        $queryBuilder = $this->dbal->createQueryBuilder();
        $response = $queryBuilder->select('*')->from('threads')
            ->where('id = ?')->setParameters([$id])
            ->fetchAssociative();

        if(!$response){
            throw new OutOfBoundsException("Thread with id {$id} does not exist.");
        }

        $guild = new GuildRepository($this->dbal);

        $thread = new Thread();
        $thread->setId($response['id']);
        $thread->setDiscordId($response['discord_id']);
        $thread->setGuild($guild->findById($response['guild_id']));
        $thread->setCreatedAt(Carbon::parse($response['created_at']));
        $thread->setModifiedAt(Carbon::parse($response['modified_at']));

        return $thread;
    }

    public function findByPart(\Discord\Parts\Thread\Thread|Part $part): Thread
    {
        $queryBuilder = $this->dbal->createQueryBuilder();
        $response = $queryBuilder->select('*')->from('threads')
            ->where('id = ?')->setParameters([$part->id])
            ->fetchAssociative();

        if(!$response){
            throw new OutOfBoundsException("Thread with id {$part->id} does not exist.");
        }

        $guild = new GuildRepository($this->dbal);

        $thread = new Thread();
        $thread->setId($response['id']);
        $thread->setDiscordId($response['discord_id']);
        $thread->setGuild($guild->findById($response['guild_id']));
        $thread->setCreatedAt(Carbon::parse($response['created_at']));
        $thread->setModifiedAt(Carbon::parse($response['modified_at']));

        return $thread;
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
                $thread = new Thread();
                $thread->setId($row['id']);
                $thread->setDiscordId($row['discord_id']);
                $thread->setGuild($guild->findById($row['guild_id']));
                $thread->setCreatedAt(Carbon::parse($row['created_at']));
                $thread->setModifiedAt(Carbon::parse($row['modified_at']));

                $collection->push($thread);
            }
        }

        return $collection;
    }

    public function save(Thread|Model $model): bool
    {
        // TODO: Implement save() method.
    }

    public function remove(Thread|Model $model): bool
    {
        // TODO: Implement remove() method.
    }
}