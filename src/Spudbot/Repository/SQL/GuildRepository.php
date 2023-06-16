<?php
declare(strict_types=1);

namespace Spudbot\Repository\SQL;

use Carbon\Carbon;
use Discord\Parts\Part;
use Doctrine\DBAL\Cache\QueryCacheProfile;
use InvalidArgumentException;
use OutOfBoundsException;
use Spudbot\Collection;
use Spudbot\Interface\IGuildRepository;
use Spudbot\Model\Event;
use Spudbot\Model\Guild;
use Spudbot\Traits\UsesDoctrine;

class GuildRepository extends IGuildRepository
{
    use UsesDoctrine;
    public function findById(string|int $id): Guild
    {
        $queryBuilder = $this->dbal->createQueryBuilder();
        $response = $queryBuilder->select('*')->from('guilds')
            ->where('id = ?')->setParameters([$id])
            ->enableResultCache(new QueryCacheProfile('300', "guild_{$id}"))
            ->fetchAssociative();

        if(!$response){
            throw new OutOfBoundsException("Guild with id {$id} does not exist.");
        }

        return Guild::withDatabaseRow($response);
    }

    public function findByDiscordId(string $discordId): Guild
    {
        $queryBuilder = $this->dbal->createQueryBuilder();
        $response = $queryBuilder->select('*')->from('guilds')
            ->where('discord_id = ?')->setParameters([$discordId])
            ->enableResultCache(new QueryCacheProfile('300', "guild_{$discordId}"))
            ->fetchAssociative();

        if(!$response){
            throw new OutOfBoundsException("Guild with id {$discordId} does not exist.");
        }

        return Guild::withDatabaseRow($response);
    }

    public function findByPart(\Discord\Parts\Guild\Guild $guild): Guild
    {
        $queryBuilder = $this->dbal->createQueryBuilder();

        $response = $queryBuilder->select('*')->from('guilds')
            ->where('discord_id = ?')
            ->setParameters([$guild->id])
            ->enableResultCache(new QueryCacheProfile('300', "guild_{$guild->id}"))
            ->fetchAssociative();

        if(!$response){
            throw new OutOfBoundsException("Guild with id {$guild->id} does not exist.");
        }

        return Guild::withDatabaseRow($response);
    }

    public function getAll(): Collection
    {
        $collection = new Collection();
        $queryBuilder = $this->dbal->createQueryBuilder();

        $response = $queryBuilder->select('*')->from('guilds')
            ->enableResultCache(new QueryCacheProfile('60', "guild_list"))
            ->fetchAllAssociative();

        if(!empty($response)){
            foreach ($response as $row) {
                $guild = Guild::withDatabaseRow($row);

                $collection->push($guild);
            }
        }

        return $collection;
    }

    public function save(Guild $guild): bool
    {
        $guild->setModifiedAt(Carbon::now());

        if(!$guild->getId()){
            $guild->setCreatedAt(Carbon::now());

            $columns = [
                'discord_id' => '?',
                'output_channel_id' => '?',
                'output_thread_id' => '?',
                'created_at' => '?',
                'modified_at' => '?',
            ];

            $parameters = [
                $guild->getDiscordId(),
                $guild->getOutputChannelId(),
                $guild->getOutputThreadId(),
                $guild->getCreatedAt()->toDateTimeString(),
                $guild->getModifiedAt()->toDateTimeString(),
            ];

            $impactedRows = $this->dbal->createQueryBuilder()
                ->insert('guilds')->values($columns)->setParameters($parameters)
                ->executeStatement();
            $guild->setId($this->dbal->lastInsertId());

            return $impactedRows > 0;
        }

        $parameters = [
            $guild->getOutputChannelId(),
            $guild->getOutputThreadId(),
            $guild->getModifiedAt()->toDateTimeString(),
            $guild->getId(),
        ];

        $impactedRows = $this->dbal->createQueryBuilder()
            ->update('guilds')
            ->set('output_channel_id', '?')
            ->set('output_thread_id', '?')
            ->set('modified_at', '?')
            ->where('id = ?')
            ->setParameters($parameters)
            ->executeStatement();

        return $impactedRows > 0;
    }

    public function remove(Guild $guild): bool
    {
        if(!$guild->getId()){
            Throw New OutOfBoundsException("Guild is unable to be removed without a proper id.");
        }

        $impactedRows = $this->dbal->createQueryBuilder()
            ->delete('guilds')->where('id = ?')->setParameter(0, $guild->getId())
            ->executeStatement();
        if($impactedRows === 0){
            Throw New \RuntimeException("Removing guild #{$guild->getId()} was unsuccessful");
        }

        $eventRepository = new EventRepository($this->dbal);
        $events = $eventRepository->findByGuild($guild);

        if(!empty($events)){
            /* @var Event $event */
            foreach ($events as $event) {
                $eventRepository->remove($event);
            }
        }

        $this->dbal->createQueryBuilder()
            ->delete('members')->where('guild_id = ?')->setParameter(0, $guild->getId())
            ->executeStatement();

        $this->dbal->createQueryBuilder()
            ->delete('threads')->where('guild_id = ?')->setParameter(0, $guild->getId())
            ->executeStatement();

        return true;
    }
}