<?php
declare(strict_types=1);

namespace Spudbot\Repository\SQL;

use Carbon\Carbon;
use Discord\Parts\Part;
use InvalidArgumentException;
use OutOfBoundsException;
use Spudbot\Collection;
use Spudbot\Interface\IGuildRepository;
use Spudbot\Model;
use Spudbot\Model\Guild;
use Spudbot\Repository\SQLRepository;
use Spudbot\Traits\UsesDoctrine;

class GuildRepository extends IGuildRepository
{
    use UsesDoctrine;
    public function findById(string|int $id): Guild
    {
        if($this->isCached($id)){
            return $this->getCache($id);
        }
        $queryBuilder = $this->dbal->createQueryBuilder();
        $response = $queryBuilder->select('*')->from('guilds')
            ->where('id = ?')->setParameters([$id])
            ->fetchAssociative();

        if(!$response){
            throw new OutOfBoundsException("Guild with id {$id} does not exist.");
        }

        $guild = new Guild();
        $guild->setId($response['id']);
        $guild->setDiscordId($response['discord_id']);
        $guild->setOutputChannelId($response['output_channel_id']);
        $guild->setOutputThreadId($response['output_thread_id']);
        $guild->setCreatedAt(Carbon::parse($response['created_at']));
        $guild->setModifiedAt(Carbon::parse($response['modified_at']));

        $this->setCache($guild->getId(), $guild);

        return $guild;
    }

    public function findByPart(\Discord\Parts\Guild\Guild|Part $part): Guild
    {
        if(!$part instanceof \Discord\Parts\Guild\Guild){
            throw new InvalidArgumentException("Part is not an instance of Guild.");
        }
        if($this->isCached($part->id)){
            return $this->getCache($part->id);
        }
        $queryBuilder = $this->dbal->createQueryBuilder();

        $response = $queryBuilder->select('*')->from('guilds')
            ->where('discord_id = ?')
            ->setParameters([$part->id])
            ->fetchAssociative();

        if(!$response){
            throw new OutOfBoundsException("Guild with id {$part->id} does not exist.");
        }

        $guild = new Guild();
        $guild->setId($response['id']);
        $guild->setDiscordId($response['discord_id']);
        $guild->setOutputChannelId($response['output_channel_id']);
        $guild->setOutputThreadId($response['output_thread_id']);
        $guild->setCreatedAt(Carbon::parse($response['created_at']));
        $guild->setModifiedAt(Carbon::parse($response['modified_at']));

        $this->setCache($guild->getId(), $guild);

        return $guild;
    }

    public function getAll(): Collection
    {
        $collection = new Collection();
        $queryBuilder = $this->dbal->createQueryBuilder();

        $response = $queryBuilder->select('*')->from('guilds')
            ->fetchAllAssociative();

        if(!empty($response)){
            foreach ($response as $row) {
                $guild = new Guild();
                $guild->setId($row['id']);
                $guild->setDiscordId($row['discord_id']);
                $guild->setOutputChannelId($row['output_channel_id']);
                $guild->setOutputThreadId($row['output_thread_id']);
                $guild->setCreatedAt(Carbon::parse($row['created_at']));
                $guild->setModifiedAt(Carbon::parse($row['modified_at']));

                if(!$this->isCached($guild->getId())){
                    $this->setCache($guild->getId(), $guild);
                }

                $collection->push($guild);
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

    public function findByDiscordId(string $discordId): Guild
    {
        // TODO: Implement findByDiscordId() method.
    }
}