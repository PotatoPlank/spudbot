<?php
declare(strict_types=1);

namespace Spudbot\Repository\SQL;

use Carbon\Carbon;
use OutOfBoundsException;
use Spudbot\Collection;
use Spudbot\Interface\IThreadRepository;
use Spudbot\Model;
use Spudbot\Model\Guild;
use Spudbot\Model\Thread;
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

        return Thread::withDatabaseRow($response, $guild->findById($response['guild_id']));
    }

    public function findByPart(\Discord\Parts\Thread\Thread $thread): Thread
    {
        $queryBuilder = $this->dbal->createQueryBuilder();
        $response = $queryBuilder->select('*')->from('threads')
            ->where('discord_id = ?')->setParameters([$thread->id])
            ->fetchAssociative();

        if(!$response){
            throw new OutOfBoundsException("Thread with id {$thread->id} does not exist.");
        }

        $guild = new GuildRepository($this->dbal);

        return Thread::withDatabaseRow($response, $guild->findById($response['guild_id']));
    }

    public function findByDiscordId(string $discordId): Thread
    {
        $queryBuilder = $this->dbal->createQueryBuilder();
        $response = $queryBuilder->select('*')->from('threads')
            ->where('discord_id = ?')->setParameters([$discordId])
            ->fetchAssociative();

        if(!$response){
            throw new OutOfBoundsException("Thread with id {$discordId} does not exist.");
        }

        $guild = new GuildRepository($this->dbal);

        return Thread::withDatabaseRow($response, $guild->findById($response['guild_id']));
    }

    public function findByGuild(Guild $guild): Collection
    {
        $collection = new Collection();
        $queryBuilder = $this->dbal->createQueryBuilder();

        $response = $queryBuilder->select('*')->from('threads')
            ->where('guild_id = ?')->setParameters([$guild->getId()])
            ->fetchAllAssociative();

        if(!empty($response)){
            foreach ($response as $row) {
                $thread = Thread::withDatabaseRow($row, $guild);

                $collection->push($thread);
            }
        }

        return $collection;
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
                $thread = Thread::withDatabaseRow($row, $guild->findById($row['guild_id']));

                $collection->push($thread);
            }
        }

        return $collection;
    }

    public function save(Thread $thread): bool
    {
        $thread->setModifiedAt(Carbon::now());

        if(!$thread->getId()){
            $thread->setCreatedAt(Carbon::now());

            $columns = [
                'discord_id' => '?',
                'guild_id' => '?',
                'created_at' => '?',
                'modified_at' => '?',
            ];

            $parameters = [
                $thread->getDiscordId(),
                $thread->getGuild()->getId(),
                $thread->getCreatedAt()->toDateTimeString(),
                $thread->getModifiedAt()->toDateTimeString(),
            ];

            $impactedRows = $this->dbal->createQueryBuilder()
                ->insert('threads')->values($columns)->setParameters($parameters)
                ->executeStatement();
            $thread->setId($this->dbal->lastInsertId());

            return $impactedRows > 0;
        }

        $parameters = [
            $thread->getModifiedAt()->toDateTimeString(),
            $thread->getId(),
        ];

        $impactedRows = $this->dbal->createQueryBuilder()
            ->update('threads')
            ->set('modified_at', '?')
            ->where('id = ?')
            ->setParameters($parameters)
            ->executeStatement();

        return $impactedRows > 0;
    }

    public function remove(Thread $thread): bool
    {
        if(!$thread->getId()){
            Throw New OutOfBoundsException("Thread is unable to be removed without a proper id.");
        }

        $impactedRows = $this->dbal->createQueryBuilder()
            ->delete('threads')->where('id = ?')->setParameter(0, $thread->getId())
            ->executeStatement();

        if($impactedRows === 0){
            Throw New \RuntimeException("Removing thread #{$thread->getId()} was unsuccessful");
        }

        return true;
    }
}