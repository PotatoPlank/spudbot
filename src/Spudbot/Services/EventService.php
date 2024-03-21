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
use Spudbot\Repositories\EventAttendanceRepository;
use Spudbot\Repositories\EventRepository;
use Spudbot\Types\EventType;

class EventService
{
    public function __construct(
        public EventRepository $eventRepository,
        public EventAttendanceRepository $attendanceRepository,
        public GuildService $guildService
    ) {
    }

    public function findOrCreateNativeWithPart(ScheduledEvent $event): Event
    {
        try {
            $model = $this->eventRepository->findWithPart($event);
            if ($model) {
                return $model;
            }
            throw new OutOfBoundsException('Does not exist.');
        } catch (OutOfBoundsException $exception) {
            return $this->eventRepository->save(Event::create([
                'native_event_id' => $event->guild_scheduled_event_id ?? $event->id,
                'type' => EventType::Native,
                'guild' => $this->guildService->findOrCreateWithPart($event->guild),
                'name' => $event->name ?? '',
                'scheduled_at' => $event->scheduled_start_time,
                'discord_channel_id' => null,
                'sesh_message_id' => null,
            ]));
        }
    }

    public function findWhereId(string $eventId): ?Event
    {
        try {
            return $this->eventRepository->findById($eventId);
        } catch (OutOfBoundsException) {
            return null;
        }
    }

    public function getAttendance(Event $event): Collection
    {
        return $this->attendanceRepository->getEventAttendance($event);
    }

    public function findOrCreateSesh(string $seshId, $defaults = []): ?Event
    {
        try {
            $model = $this->eventRepository->findBySeshId($seshId);
            if ($model) {
                return $model;
            }
            throw new OutOfBoundsException('Does not exist.');
        } catch (OutOfBoundsException $exception) {
            return $this->eventRepository->save(Event::create($defaults));
        }
    }
}
