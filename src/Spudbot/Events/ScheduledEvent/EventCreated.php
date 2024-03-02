<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023-2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Events\ScheduledEvent;


use DI\Attribute\Inject;
use Discord\WebSockets\Event;
use Spudbot\Interface\AbstractEventSubscriber;
use Spudbot\Services\EventService;
use Spudbot\Services\GuildService;

class EventCreated extends AbstractEventSubscriber
{
    #[Inject]
    protected GuildService $guildService;
    #[Inject]
    protected EventService $eventService;

    public function getEventName(): string
    {
        return Event::GUILD_SCHEDULED_EVENT_CREATE;
    }

    public function update($event = null): void
    {
        $guild = $this->spud->discord->guilds->get('id', $event->guild_id);
        if (!$guild) {
            return;
        }
        $eventPart = $guild->guild_scheduled_events->get('id', $event->guild_scheduled_event_id);
        if (!$eventPart) {
            return;
        }
        $this->eventService->findOrCreateNativeWithPart($eventPart);
    }
}
