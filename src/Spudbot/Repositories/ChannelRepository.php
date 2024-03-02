<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023-2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

declare(strict_types=1);

namespace Spudbot\Repositories;

use Carbon\Carbon;
use GuzzleHttp\Exception\GuzzleException;
use OutOfBoundsException;
use Spudbot\Helpers\Collection;
use Spudbot\Interface\IChannelRepository;
use Spudbot\Model\Channel;
use Spudbot\Model\Guild;
use Spudbot\Traits\UsesApi;

class ChannelRepository extends IChannelRepository
{
    use UsesApi;

    public function findById(string|int $id): Channel
    {
        $response = $this->client->get("channels/{$id}");
        $json = $this->getResponseJson($response);

        if (!$json) {
            throw new OutOfBoundsException("Channel with id {$id} does not exist.");
        }

        return Channel::hydrateWithArray($json);
    }

    public function findByGuild(Guild $guild): Collection
    {
        $collection = new Collection();

        $response = $this->client->get('channels', [
            'query' => [
                'guild' => $guild->getId(),
            ],
        ]);
        $json = $this->getResponseJson($response);

        if (!empty($json)) {
            foreach ($json['data'] as $row) {
                $channel = Channel::hydrateWithArray($row);

                $collection->push($channel);
            }
        }

        return $collection;
    }

    public function getAll(): Collection
    {
        $collection = new Collection();

        $response = $this->client->get('channels');
        $json = $this->getResponseJson($response);

        if (!empty($json)) {
            foreach ($json['data'] as $row) {
                $channel = Channel::hydrateWithArray($row);

                $collection->push($channel);
            }
        }

        return $collection;
    }

    /**
     * @throws ApiException
     * @throws GuzzleException
     */
    public function save(Channel $channel): Channel
    {
        $channel->setModifiedAt(Carbon::now());

        $params = [
            'discord_id' => $channel->getDiscordId(),
            'guild_id' => $channel->getGuild()->getId(),
        ];

        if (!$channel->getId()) {
            $channel->setCreatedAt(Carbon::now());
            $response = $this->client->post("channels", [
                'json' => $params,
            ]);
        } else {
            $response = $this->client->put("channels/{$channel->getId()}", [
                'json' => $params,
            ]);
        }

        if (!$this->wasSuccessful($response)) {
            throw new ApiException();
        }

        return $channel;
    }

    public function remove(Channel $channel): bool
    {
        if (!$channel->getId()) {
            throw new OutOfBoundsException("Channel is unable to be removed without a proper id.");
        }

        $response = $this->client->delete("channels/{$channel->getId()}");
        $json = $this->getResponseJson($response);
        if (!$json['success']) {
            throw new \RuntimeException("Removing channel {$channel->getId()} was unsuccessful");
        }

        return true;
    }

    public function findByPart(\Discord\Parts\Channel\Channel $channel): Channel
    {
        return $this->findByDiscordId($channel->id, $channel->guild->id);
    }

    public function findByDiscordId(string $discordId, string $discordGuildId): Channel
    {
        $response = $this->client->get('channels', [
            'query' => [
                'discord_id' => $discordId,
            ],
        ]);
        $json = $this->getResponseJson($response);

        if (!$json) {
            throw new OutOfBoundsException("Channel with discord id {$discordId} does not exist.");
        }

        return Channel::hydrateWithArray($json['data'][0]);
    }
}
