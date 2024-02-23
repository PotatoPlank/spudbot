<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

declare(strict_types=1);

namespace Spudbot\Repository\Api;

use Carbon\Carbon;
use OutOfBoundsException;
use Spudbot\Helpers\Collection;
use Spudbot\Interface\IGuildRepository;
use Spudbot\Model\Guild;
use Spudbot\Traits\UsesApi;

class GuildRepository extends IGuildRepository
{
    use UsesApi;

    public function findById(string|int $id): Guild
    {
        $response = $this->client->get("guilds/{$id}");
        $json = $this->getResponseJson($response);

        if (!$json) {
            throw new OutOfBoundsException("Guild with id {$id} does not exist.");
        }

        return Guild::hydrateWithArray($json);
    }

    public function findByPart(\Discord\Parts\Guild\Guild $guild): Guild
    {
        return $this->findByDiscordId($guild->id);
    }

    public function findByDiscordId(string $discordId): Guild
    {
        $response = $this->client->get("guilds", [
            'query' => [
                'discord_id' => $discordId,
            ],
        ]);
        $json = $this->getResponseJson($response);

        if (!$json) {
            throw new OutOfBoundsException("Guild with id {$discordId} does not exist.");
        }

        return Guild::hydrateWithArray($json['data']);
    }

    public function getAll(): Collection
    {
        $collection = new Collection();

        $response = $this->client->get('guilds');
        $json = $this->getResponseJson($response);


        if (!empty($json)) {
            foreach ($json['data'] as $row) {
                $guild = Guild::hydrateWithArray($row);

                $collection->push($guild);
            }
        }

        return $collection;
    }

    public function save(Guild $guild): bool
    {
        $guild->setModifiedAt(Carbon::now());

        $params = [
            'channel_announce_id' => $guild->getOutputChannelId(),
            'channel_thread_announce_id' => $guild->getOutputThreadId(),
        ];

        if (!$guild->getId()) {
            $guild->setCreatedAt(Carbon::now());

            $params = [
                'discord_id' => $guild->getDiscordId(),
                ...$params,
            ];
            $response = $this->client->post("guilds", [
                'json' => $params,
            ]);
        } else {
            $response = $this->client->put("guilds/{$guild->getId()}", [
                'json' => $params,
            ]);
        }
        $json = $this->getResponseJson($response);

        return (bool)$json['success'];
    }

    public function remove(Guild $guild): bool
    {
        if (!$guild->getId()) {
            throw new OutOfBoundsException("Guild is unable to be removed without a proper id.");
        }

        $response = $this->client->delete("guilds/{$guild->getId()}");
        $json = $this->getResponseJson($response);
        if (!$json['success']) {
            throw new \RuntimeException("Removing guild {$guild->getId()} was unsuccessful");
        }

        return true;
    }
}
