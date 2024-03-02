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
use Spudbot\Interface\IDirectoryRepository;
use Spudbot\Model\Channel;
use Spudbot\Model\Directory;
use Spudbot\Model\Guild;
use Spudbot\Traits\UsesApi;

class DirectoryRepository extends IDirectoryRepository
{
    use UsesApi;

    public function findByGuild(Guild $guild): Collection
    {
        $collection = new Collection();

        $response = $this->client->get('directories', [
            'query' => [
                'guild' => $guild->getId(),
            ],
        ]);
        $json = $this->getResponseJson($response);

        if (!empty($json)) {
            foreach ($json['data'] as $row) {
                $directory = Directory::hydrateWithArray($row);

                $collection->push($directory);
            }
        }

        return $collection;
    }

    public function findByForumChannel(Channel $channel): Directory
    {
        $response = $this->client->get('directories', [
            'query' => [
                'forum_channel' => $channel->getId(),
            ],
        ]);
        $json = $this->getResponseJson($response);

        if (!$json) {
            throw new OutOfBoundsException("Directory with forum {$channel->getId()} does not exist.");
        }

        return Directory::hydrateWithArray($json['data'][0]);
    }

    public function findByDirectoryChannel(Channel $channel): Collection
    {
        $collection = new Collection();

        $response = $this->client->get('directories', [
            'query' => [
                'directory_channel' => $channel->getId(),
            ],
        ]);
        $json = $this->getResponseJson($response);


        if (!empty($json)) {
            foreach ($json['data'] as $row) {
                $directory = Directory::hydrateWithArray($row);

                $collection->push($directory);
            }
        }

        return $collection;
    }

    public function getAll(): Collection
    {
        $collection = new Collection();

        $response = $this->client->get('directories');
        $json = $this->getResponseJson($response);

        if (!empty($json)) {
            foreach ($json['data'] as $row) {
                $directory = Directory::hydrateWithArray($row);

                $collection->push($directory);
            }
        }

        return $collection;
    }

    public function findById(int|string $id): Directory
    {
        $response = $this->client->get("directories/{$id}");
        $json = $this->getResponseJson($response);

        if (!$json) {
            throw new OutOfBoundsException("Directory with id {$id} does not exist.");
        }

        return Directory::hydrateWithArray($json);
    }

    public function remove(Directory $directory): bool
    {
        if (!$directory->getId()) {
            throw new OutOfBoundsException("Directory is unable to be removed without a proper id.");
        }
        $response = $this->client->delete("directories/{$directory->getId()}");
        $json = $this->getResponseJson($response);

        if (!$json['success']) {
            throw new \RuntimeException("Removing directory #{$directory->getId()} was unsuccessful");
        }

        return true;
    }

    /**
     * @throws ApiException
     * @throws GuzzleException
     */
    public function save(Directory $directory): Directory
    {
        $directory->setModifiedAt(Carbon::now());
        $params = [
            'embed_id' => $directory->getEmbedId(),
            'directory_channel' => $directory->getDirectoryChannel()->getId(),
            'forum_channel' => $directory->getForumChannel()->getId(),
        ];

        if (!$directory->getId()) {
            $directory->setCreatedAt(Carbon::now());

            $response = $this->client->post("directories", [
                'json' => $params,
            ]);
        } else {
            return $directory;
        }

        if (!$this->wasSuccessful($response)) {
            throw new ApiException();
        }

        return $directory;
    }
}
