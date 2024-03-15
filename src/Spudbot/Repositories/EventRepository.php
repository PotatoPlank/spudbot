<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023-2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

declare(strict_types=1);

namespace Spudbot\Repositories;

use Discord\Parts\Guild\ScheduledEvent;
use Discord\Parts\Part;
use InvalidArgumentException;
use Spudbot\Model\Event;
use stdClass;

/**
 * @method Event findById(string $id)
 * @method Event save(Event $model)
 * @method bool remove(Event $model)
 */
class EventRepository extends AbstractRepository
{

    protected array $endpoints = [
        'default' => 'events',
        'put' => 'put|events/:id',
        'delete' => 'delete|events/:id',
    ];

    public function findBySeshId(string $seshId): Event
    {
        $response = $this->find([
            'query' => [
                'sesh_id' => $seshId,
            ],
        ]);

        return $response->first();
    }

    public function findWithPart(stdClass|ScheduledEvent|Part $part): Event
    {
        if (!($part instanceof ScheduledEvent) && !isset($part->guild_scheduled_event_id)) {
            throw new InvalidArgumentException("Part is not an instance with an Event Id.");
        }

        $id = $part instanceof ScheduledEvent ? $part->id : $part->guild_scheduled_event_id;

        return $this->findByDiscordId($id, $part->guild_id)->first();
    }

    public function hydrate(array $fields): Event
    {
        return Event::create([
            'external_id' => $fields['external_id'],
            'guild' => $fields['guild'],
            'channelId' => $fields['discord_channel_id'],
            'name' => $fields['name'],
            'type' => $fields['type'],
            'seshId' => $fields['sesh_message_id'],
            'nativeId' => $fields['native_event_id'],
            'scheduledAt' => $fields['scheduled_at'],
            'createdAt' => $fields['created_at'],
            'updatedAt' => $fields['updated_at'],
        ]);
    }
}
