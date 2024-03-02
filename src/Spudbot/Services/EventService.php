<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Services;

use Discord\Parts\Guild\ScheduledEvent;
use OutOfBoundsException;
use Spudbot\Helpers\Collection;
use Spudbot\Model\Event;
use Spudbot\Repositories\EventRepository;
use Spudbot\Types\EventType;

class EventService
{
    public function __construct(public EventRepository $eventRepository, public GuildService $guildService)
    {
    }

    public function findOrCreateNativeWithPart(ScheduledEvent $event): Event
    {
        try {
            return $this->eventRepository->findByPart($event);
        } catch (OutOfBoundsException $exception) {
            return $this->eventRepository->save(Event::create([
                'nativeId' => $event->guild_scheduled_event_id ?? $event->id,
                'type' => EventType::Native,
                'guild' => $this->guildService->findWithPart($event->guild),
                'name' => $event->name ?? '',
                'scheduledAt' => $event->scheduled_start_time,
            ]));
        }
    }

    public function getAttendance(Event $event): Collection
    {
        return $this->eventRepository->getAttendanceByEvent($event);
    }

    public function findOrCreateSesh(string $seshId, $defaults = []): Event
    {
        try {
            return $this->eventRepository->findBySeshId($seshId);
        } catch (OutOfBoundsException $exception) {
            return $this->eventRepository->save(Event::create($defaults));
        }
    }
}
