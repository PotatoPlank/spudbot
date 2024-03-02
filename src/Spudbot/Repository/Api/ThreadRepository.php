<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023-2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

declare(strict_types=1);

namespace Spudbot\Repository\Api;

use Carbon\Carbon;
use GuzzleHttp\Exception\GuzzleException;
use OutOfBoundsException;
use Spudbot\Helpers\Collection;
use Spudbot\Interface\IThreadRepository;
use Spudbot\Model\Guild;
use Spudbot\Model\Thread;
use Spudbot\Traits\UsesApi;

class ThreadRepository extends IThreadRepository
{
    use UsesApi;

    public function findById(string|int $id): Thread
    {
        $response = $this->client->get("threads/{$id}");
        $json = $this->getResponseJson($response);

        if (!$json) {
            throw new OutOfBoundsException("Thread with id {$id} does not exist.");
        }

        return Thread::hydrateWithArray($json);
    }

    public function findByPart(\Discord\Parts\Thread\Thread $thread): Thread
    {
        return $this->findByDiscordId($thread->id, $thread->guild->id, $thread->parent->id);
    }

    public function findByDiscordId(string $discordId, string $discordGuildId, ?string $discordChannelId = null): Thread
    {
        $response = $this->client->get("threads", [
            'query' => [
                'discord_id' => $discordId,
            ],
        ]);
        $json = $this->getResponseJson($response);

        if (!$json) {
            throw new OutOfBoundsException("Thread with id {$discordId} does not exist.");
        }

        return Thread::hydrateWithArray($json['data'][0]);
    }

    /**
     * @throws ApiException
     * @throws GuzzleException
     */
    public function save(Thread $thread): Thread
    {
        $thread->setModifiedAt(Carbon::now());

        $params = [
            'tag' => $thread->getTag(),
        ];

        if (!$thread->getId()) {
            $thread->setCreatedAt(Carbon::now());

            $params = [
                'discord_id' => $thread->getDiscordId(),
                'guild' => $thread->getGuild()->getId(),
                'channel' => $thread->getChannel()->getId(),
                ...$params,
            ];
            $response = $this->client->post("threads", [
                'json' => $params,
            ]);
        } else {
            $response = $this->client->put("threads/{$thread->getId()}", [
                'json' => $params,
            ]);
        }

        if (!$this->wasSuccessful($response)) {
            throw new ApiException();
        }


        return $thread;
    }

    public function findByGuild(Guild $guild): Collection
    {
        $collection = new Collection();

        $response = $this->client->get('threads', [
            'query' => [
                'guild' => $guild->getId(),
            ],
        ]);
        $json = $this->getResponseJson($response);

        if (!empty($json)) {
            foreach ($json['data'] as $row) {
                $thread = Thread::hydrateWithArray($row);

                $collection->push($thread);
            }
        }

        return $collection;
    }

    public function getAll(): Collection
    {
        $collection = new Collection();

        $response = $this->client->get('threads');
        $json = $this->getResponseJson($response);

        if (!empty($json)) {
            foreach ($json['data'] as $row) {
                $thread = Thread::hydrateWithArray($row);

                $collection->push($thread);
            }
        }

        return $collection;
    }

    public function remove(Thread $thread): bool
    {
        if (!$thread->getId()) {
            throw new OutOfBoundsException("Thread is unable to be removed without a proper id.");
        }

        $response = $this->client->delete("threads/{$thread->getId()}");
        $json = $this->getResponseJson($response);
        if (!$json['success']) {
            throw new \RuntimeException("Removing thread {$thread->getId()} was unsuccessful");
        }

        return true;
    }
}
