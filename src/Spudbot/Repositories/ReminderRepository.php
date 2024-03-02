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
use Spudbot\Interface\IReminderRepository;
use Spudbot\Model\Channel;
use Spudbot\Model\Guild;
use Spudbot\Model\Reminder;
use Spudbot\Traits\UsesApi;

class ReminderRepository extends IReminderRepository
{
    use UsesApi;

    public function findById(string|int $id): Reminder
    {
        $response = $this->client->get("reminders/{$id}");
        $json = $this->getResponseJson($response);

        if (!$json) {
            throw new OutOfBoundsException("Reminder with id {$id} does not exist.");
        }

        return Reminder::hydrateWithArray($json);
    }

    public function findByGuild(Guild $guild): Collection
    {
        $collection = new Collection();

        $response = $this->client->get('reminders', [
            'query' => [
                'guild' => $guild->getId(),
            ],
        ]);
        $json = $this->getResponseJson($response);

        if (!empty($json)) {
            foreach ($json['data'] as $row) {
                $reminder = Reminder::hydrateWithArray($row);

                $collection->push($reminder);
            }
        }

        return $collection;
    }

    public function getAll(): Collection
    {
        $collection = new Collection();

        $response = $this->client->get('reminders');
        $json = $this->getResponseJson($response);

        if (!empty($json)) {
            foreach ($json['data'] as $row) {
                $reminder = Reminder::hydrateWithArray($row);

                $collection->push($reminder);
            }
        }

        return $collection;
    }

    /**
     * @throws ApiException
     * @throws GuzzleException
     */
    public function save(Reminder $reminder): Reminder
    {
        $reminder->setModifiedAt(Carbon::now());

        $params = [

            'description' => $reminder->getDescription(),
            'mention_role' => $reminder->getMentionableRole(),
            'repeats' => $reminder->getRepeats(),
            'scheduled_at' => $reminder->getScheduledAt()->toIso8601String(),
        ];

        if (!$reminder->getId()) {
            $reminder->setCreatedAt(Carbon::now());

            $params = [
                'guild' => $reminder->getGuild()->getId(),
                'channel' => $reminder->getChannel()->getId(),
                ...$params,
            ];

            $response = $this->client->post("reminders", [
                'json' => $params,
            ]);
        } else {
            $response = $this->client->put("reminders/{$reminder->getId()}", [
                'json' => $params,
            ]);
        }
        if (!$this->wasSuccessful($response)) {
            throw new ApiException();
        }

        return $reminder;
    }

    public function remove(Reminder $reminder): bool
    {
        if (!$reminder->getId()) {
            throw new OutOfBoundsException("Reminder is unable to be removed without a proper id.");
        }

        $response = $this->client->delete("reminders/{$reminder->getId()}");
        $json = $this->getResponseJson($response);

        if (!$json['success']) {
            throw new \RuntimeException("Removing reminder {$reminder->getId()} was unsuccessful");
        }

        return true;
    }

    public function findByDate(Carbon $scheduledAt): Collection
    {
        throw new \BadMethodCallException("Unable to currently lookup reminder by date.");

        $collection = new Collection();

        $response = $this->client->get('reminders', [
            'query' => [
                'scheduled_at' => $scheduledAt->toDate(),
            ],
        ]);
        $json = $this->getResponseJson($response);

        if (!empty($json)) {
            foreach ($json['data'] as $row) {
                $reminder = Reminder::hydrateWithArray($row);

                $collection->push($reminder);
            }
        }

        return $collection;
    }

    public function findByDateTime(Carbon $scheduledAt): Reminder
    {
        throw new \BadMethodCallException("Unable to currently lookup reminder by datetime.");

        $collection = new Collection();

        $response = $this->client->get('reminders', [
            'query' => [
                'scheduled_at' => $scheduledAt->toIso8601String(),
            ],
        ]);
        $json = $this->getResponseJson($response);

        if (!empty($json)) {
            foreach ($json['data'] as $row) {
                $reminder = Reminder::hydrateWithArray($row);

                $collection->push($reminder);
            }
        }

        return $collection;
    }

    public function findByChannel(Channel $channel): Collection
    {
        throw new \BadMethodCallException("Unable to currently lookup reminder by channel instance.");

        $collection = new Collection();

        $response = $this->client->get('reminders', [
            'query' => [
                'channel' => $channel->getId(),
            ],
        ]);
        $json = $this->getResponseJson($response);

        if (!empty($json)) {
            foreach ($json['data'] as $row) {
                $reminder = Reminder::hydrateWithArray($row);

                $collection->push($reminder);
            }
        }

        return $collection;
    }

    public function findElapsed(): Collection
    {
        $collection = new Collection();

        $response = $this->client->get('reminders', [
            'query' => [
                'has_passed' => Carbon::now()->toIso8601String(),
            ],
        ]);
        $json = $this->getResponseJson($response);

        if (!empty($json)) {
            foreach ($json['data'] as $row) {
                $reminder = Reminder::hydrateWithArray($row);

                $collection->push($reminder);
            }
        }

        return $collection;
    }
}
