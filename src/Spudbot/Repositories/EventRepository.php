<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023-2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

declare(strict_types=1);

namespace Spudbot\Repositories;

use Carbon\Carbon;
use Discord\Parts\Guild\ScheduledEvent;
use GuzzleHttp\Exception\GuzzleException;
use InvalidArgumentException;
use OutOfBoundsException;
use Spudbot\Helpers\Collection;
use Spudbot\Interface\IEventRepository;
use Spudbot\Model\Event;
use Spudbot\Model\EventAttendance;
use Spudbot\Model\Guild;
use Spudbot\Model\Member;
use Spudbot\Traits\UsesApi;

class EventRepository extends IEventRepository
{
    use UsesApi;

    public function findById(string|int $id): Event
    {
        $response = $this->client->get("events/{$id}");
        $json = $this->getResponseJson($response);

        if (!$json) {
            throw new OutOfBoundsException("Event with id {$id} does not exist.");
        }

        return Event::hydrateWithArray($json);
    }

    public function findByPart(\stdClass|ScheduledEvent $event): Event
    {
        if (!($event instanceof ScheduledEvent) && !isset($event->guild_scheduled_event_id)) {
            throw new InvalidArgumentException("Part is not an instance with an Event Id.");
        }

        $id = $event instanceof ScheduledEvent ? $event->id : $event->guild_scheduled_event_id;

        return $this->findByDiscordId($id, $event->guild_id);
    }

    public function findByDiscordId(string $discordId, string $discordGuildId): Event
    {
        $response = $this->client->get('events', [
            'query' => [
                'native_id' => $discordId,
                'guild_discord_id' => $discordGuildId,
            ],
        ]);
        $json = $this->getResponseJson($response);

        if (!$json) {
            throw new OutOfBoundsException("Event with id {$discordId} does not exist.");
        }

        return Event::hydrateWithArray($json['data'][0]);
    }

    public function findBySeshId(string $seshId): Event
    {
        $response = $this->client->get('events', [
            'query' => [
                'sesh_id' => $seshId,
            ],
        ]);
        $json = $this->getResponseJson($response);

        if (!$json) {
            throw new OutOfBoundsException("Event with id {$seshId} does not exist.");
        }

        return Event::hydrateWithArray($json['data'][0]);
    }

    public function findByGuild(Guild $guild): Collection
    {
        $collection = new Collection();

        $response = $this->client->get('events', [
            'query' => [
                'guild' => $guild->getId(),
            ],
        ]);
        $json = $this->getResponseJson($response);

        if (!empty($json)) {
            foreach ($json['data'] as $row) {
                $event = Event::hydrateWithArray($row);

                $collection->push($event);
            }
        }

        return $collection;
    }

    public function getAll(): Collection
    {
        $collection = new Collection();

        $response = $this->client->get('events');
        $json = $this->getResponseJson($response);

        if (!empty($json)) {
            foreach ($json['data'] as $row) {
                $event = Event::hydrateWithArray($row);

                $collection->push($event);
            }
        }

        return $collection;
    }

    public function getAttendanceByMemberAndEvent(Member $member, Event $event): EventAttendance
    {
        $attendees = $this->getAttendanceByEvent($event);
        $attendees->filter(function (EventAttendance $attendance) use ($member) {
            return $attendance->getMember()->getId() === $member->getId();
        });

        if ($attendees->empty()) {
            throw new OutOfBoundsException("Event data associated with specified user and event does not exist.");
        }

        return $attendees->first();
    }

    public function getAttendanceByEvent(Event $event): Collection
    {
        $collection = new Collection();

        $response = $this->client->get("events/{$event->getId()}/attendance");
        $json = $this->getResponseJson($response);

        if (!empty($json)) {
            foreach ($json['data'] as $row) {
                $attendance = EventAttendance::hydrateWithArray($row);

                $collection->push($attendance);
            }
        }

        return $collection;
    }

    /**
     * @throws ApiException
     * @throws GuzzleException
     */
    public function save(Event $event): Event
    {
        $event->setModifiedAt(Carbon::now());

        $params = [
            'discord_channel_id' => $event->getChannelId(),
            'name' => $event->getName(),
            'scheduled_at' => $event->getScheduledAt()->toIso8601String(),
        ];

        if (!$event->getId()) {
            $event->setCreatedAt(Carbon::now());

            $params = [
                'guild' => $event->getGuild()->getId(),
                'type' => $event->getType(),
                'sesh_id' => $event->getSeshId(),
                'native_id' => $event->getNativeId(),
                ...$params,
            ];

            $response = $this->client->post("events", [
                'json' => $params,
            ]);
        } else {
            $response = $this->client->put("events/{$event->getId()}", [
                'json' => $params,
            ]);
        }

        if (!$this->wasSuccessful($response)) {
            throw new ApiException();
        }

        return $event;
    }

    public function remove(Event $event): bool
    {
        if (!$event->getId()) {
            throw new OutOfBoundsException("Event is unable to be removed without a proper id.");
        }
        $response = $this->client->delete("events/{$event->getId()}");
        $json = $this->getResponseJson($response);

        if (!$json['success']) {
            throw new \RuntimeException("Removing event {$event->getId()} was unsuccessful");
        }

        return true;
    }
}
