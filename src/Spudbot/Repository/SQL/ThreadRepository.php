<?php
declare(strict_types=1);

namespace Spudbot\Repository\SQL;

use Carbon\Carbon;
use Discord\Parts\Part;
use OutOfBoundsException;
use Spudbot\Collection;
use Spudbot\Interface\IThreadRepository;
use Spudbot\Model;
use Spudbot\Model\Guild;
use Spudbot\Model\Thread;
use Spudbot\Repository\SQLRepository;
use Spudbot\Traits\UsesDoctrine;

class ThreadRepository extends IThreadRepository
{
    use UsesDoctrine;
    public function findById(string|int $id): Thread
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

    public function findByPart(\Discord\Parts\Thread\Thread $thread): Thread
    {
        $queryBuilder = $this->dbal->createQueryBuilder();
        $response = $queryBuilder->select('*')->from('threads')
            ->where('id = ?')->setParameters([$thread->id])
            ->fetchAssociative();

        if(!$response){
            throw new OutOfBoundsException("Thread with id {$thread->id} does not exist.");
        }

        $guild = new GuildRepository($this->dbal);

        $threadModel = new Thread();
        $threadModel->setId($response['id']);
        $threadModel->setDiscordId($response['discord_id']);
        $threadModel->setGuild($guild->findById($response['guild_id']));
        $threadModel->setCreatedAt(Carbon::parse($response['created_at']));
        $threadModel->setModifiedAt(Carbon::parse($response['modified_at']));

        return $threadModel;
    }

    public function findByDiscordId(string $discordId): Thread
    {
        // TODO: Implement findByDiscordId() method.
    }

    public function findByGuild(Guild $guild): Guild
    {
        // TODO: Implement findByGuild() method.
    }

    public function getAll(): Collection
    {
        $collection = new Collection();
        $guild = new GuildRepository($this->dbal);
        $queryBuilder = $this->dbal->createQueryBuilder();

        $response = $queryBuilder->select('*')->from('threads')
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

    public function save(Thread|Model $thread): bool
    {
        // TODO: Implement save() method.
    }

    public function remove(Thread|Model $thread): bool
    {
        // TODO: Implement remove() method.
    }
}