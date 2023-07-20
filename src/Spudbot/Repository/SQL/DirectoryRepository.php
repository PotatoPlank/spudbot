<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

declare(strict_types=1);

namespace Spudbot\Repository\SQL;

use Carbon\Carbon;
use OutOfBoundsException;
use Spudbot\Helpers\Collection;
use Spudbot\Interface\IDirectoryRepository;
use Spudbot\Model\Channel;
use Spudbot\Model\Directory;
use Spudbot\Model\Guild;
use Spudbot\Model\Thread;
use Spudbot\Traits\UsesDoctrine;

class DirectoryRepository extends IDirectoryRepository
{
    use UsesDoctrine;

    private array $fields = [
        'd.id',
        'd.directory_channel_id',
        'd.forum_channel_id',
        'd.embed_id',
        'd.created_at',
        'd.modified_at',
    ];

    public function findByGuild(Guild $guild): Collection
    {
        $collection = new Collection();
        $channelRepository = new ChannelRepository($this->dbal);
        $response = $this->dbal->createQueryBuilder()
            ->select(...$this->fields)
            ->from('channels', 'c')
            ->innerJoin('c', 'directories', 'd', 'd.directory_channel_id = c.id')
            ->where('c.guild_id = ?')->setParameters([$guild->getId()])
            ->fetchAllAssociative();


        if (!empty($response)) {
            foreach ($response as $row) {
                $directoryChannel = $channelRepository->findById($row['directory_channel_id']);
                $forumChannel = $channelRepository->findById($row['forum_channel_id']);
                $directory = Directory::withDatabaseRow($response, $directoryChannel, $forumChannel);

                $collection->push($directory);
            }
        }

        return $collection;
    }

    public function findById(int|string $id): Directory
    {
        $channelRepository = new ChannelRepository($this->dbal);
        $response = $this->dbal->createQueryBuilder()
            ->select(...$this->fields)
            ->from('directories', 'd')
            ->where('d.id = ?')->setParameters([$id])
            ->fetchAssociative();

        if (!$response) {
            throw new OutOfBoundsException("Guild with id {$id} does not exist.");
        }

        $directoryChannel = $channelRepository->findById($response['directory_channel_id']);
        $forumChannel = $channelRepository->findById($response['forum_channel_id']);

        return Directory::withDatabaseRow($response, $directoryChannel, $forumChannel);
    }

    public function findByForumChannel(Channel $channel): Directory
    {
        $channelRepository = new ChannelRepository($this->dbal);
        $response = $this->dbal->createQueryBuilder()
            ->select(...$this->fields)
            ->from('directories', 'd')
            ->where('d.forum_channel_id = ?')->setParameters([$channel->getId()])
            ->fetchAssociative();

        if (!$response) {
            throw new OutOfBoundsException("Directory with forum {$channel->getId()} does not exist.");
        }

        $directoryChannel = $channelRepository->findById($response['directory_channel_id']);
        $forumChannel = $channelRepository->findById($response['forum_channel_id']);

        return Directory::withDatabaseRow($response, $directoryChannel, $forumChannel);
    }

    public function findByDirectoryChannel(Channel $channel): Collection
    {
        $collection = new Collection();
        $channelRepository = new ChannelRepository($this->dbal);
        $response = $this->dbal->createQueryBuilder()
            ->select(...$this->fields)
            ->from('directories', 'd')
            ->where('d.directory_channel_id = ?')->setParameters([$channel->getId()])
            ->fetchAllAssociative();


        if (!empty($response)) {
            foreach ($response as $row) {
                $directoryChannel = $channelRepository->findById($row['directory_channel_id']);
                $forumChannel = $channelRepository->findById($row['forum_channel_id']);
                $directory = Directory::withDatabaseRow($response, $directoryChannel, $forumChannel);

                $collection->push($directory);
            }
        }

        return $collection;
    }

    public function getAll(): Collection
    {
        $collection = new Collection();
        $channelRepository = new ChannelRepository($this->dbal);
        $response = $this->dbal->createQueryBuilder()
            ->select(...$this->fields)
            ->from('directories', 'd')
            ->fetchAllAssociative();


        if (!empty($response)) {
            foreach ($response as $row) {
                $directoryChannel = $channelRepository->findById($row['directory_channel_id']);
                $forumChannel = $channelRepository->findById($row['forum_channel_id']);
                $directory = Directory::withDatabaseRow($response, $directoryChannel, $forumChannel);

                $collection->push($directory);
            }
        }

        return $collection;
    }

    public function remove(Directory $directory): bool
    {
        if (!$directory->getId()) {
            throw new OutOfBoundsException("Directory is unable to be removed without a proper id.");
        }

        $impactedRows = $this->dbal->createQueryBuilder()
            ->delete('directories')->where('id = ?')->setParameter(0, $directory->getId())
            ->executeStatement();
        if ($impactedRows === 0) {
            throw new \RuntimeException("Removing directory #{$directory->getId()} was unsuccessful");
        }

        return true;
    }

    public function getEmbedContentFromPart(\Discord\Parts\Channel\Channel $forumChannel): string
    {
        $embedContent = '';
        if ($forumChannel->threads->count() === 0) {
            throw new \InvalidArgumentException('The provided forum channel does not have any threads.');
        }
        $threadRepository = new ThreadRepository($this->dbal);
        $channelRepository = new ChannelRepository($this->dbal);
        $guildRepository = new GuildRepository($this->dbal);
        $guild = $guildRepository->findByPart($forumChannel->guild);

        try {
            $channel = $channelRepository->findByPart($forumChannel);
        } catch (\OutOfBoundsException $exception) {
            $channel = new \Spudbot\Model\Channel();
            $channel->setGuild($guild);
            $channel->setDiscordId($forumChannel->id);
            $channelRepository->save($channel);
        }

        $categories = [
            'General' => [],
        ];
        /**
         * @var \Discord\Parts\Thread\Thread $threadPart
         */
        foreach ($forumChannel->threads as $threadPart) {
            if ($threadPart->locked || ($threadPart->archived && $threadPart->archive_timestamp->diffInWeeks() >= 2)) {
                continue;
            }
            try {
                $thread = $threadRepository->findByPart($threadPart);
            } catch (\OutOfBoundsException $exception) {
                print $exception->getMessage() . PHP_EOL;
                $thread = new Thread();
                $thread->setChannel($channel);
                $thread->setGuild($guild);
                $thread->setDiscordId($threadPart->id);
                $threadRepository->save($thread);
            }
            if (empty($thread->getTag())) {
                $categories['General'][] = $threadPart->id;
            } else {
                if (!isset($categories[$thread->getTag()])) {
                    $categories[$thread->getTag()] = [];
                }
                $categories[$thread->getTag()][] = $threadPart->id;
            }
        }
        if (!empty($categories)) {
            foreach ($categories as $category => $threads) {
                if (!empty($threads)) {
                    $embedContent .= "**{$category}**" . PHP_EOL;
                    foreach ($threads as $threadId) {
                        $embedContent .= "<#$threadId>" . PHP_EOL;
                    }
                    $embedContent .= PHP_EOL;
                }
            }
        }

        return !empty($embedContent) ? $embedContent : 'No threads found.';
    }

    public function save(Directory $directory): bool
    {
        $directory->setModifiedAt(Carbon::now());

        if (!$directory->getId()) {
            $directory->setCreatedAt(Carbon::now());

            $columns = [
                'directory_channel_id' => '?',
                'forum_channel_id' => '?',
                'embed_id' => '?',
                'created_at' => '?',
                'modified_at' => '?',
            ];

            $parameters = [
                $directory->getDirectoryChannel()->getId(),
                $directory->getForumChannel()->getId(),
                $directory->getEmbedId(),
                $directory->getCreatedAt()->toDateTimeString(),
                $directory->getModifiedAt()->toDateTimeString(),
            ];

            $impactedRows = $this->dbal->createQueryBuilder()
                ->insert('directories')->values($columns)->setParameters($parameters)
                ->executeStatement();
            $directory->setId($this->dbal->lastInsertId());

            return $impactedRows > 0;
        }

        $parameters = [
            $directory->getDirectoryChannel()->getId(),
            $directory->getForumChannel()->getId(),
            $directory->getEmbedId(),
            $directory->getModifiedAt()->toDateTimeString(),
            $directory->getId(),
        ];

        $impactedRows = $this->dbal->createQueryBuilder()
            ->update('directories')
            ->set('directory_channel_id', '?')
            ->set('forum_channel_id', '?')
            ->set('embed_id', '?')
            ->set('modified_at', '?')
            ->where('id = ?')
            ->setParameters($parameters)
            ->executeStatement();

        return $impactedRows > 0;
    }
}