<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Bindable\Event\ScheduledEvent;


use Discord\WebSockets\Event;
use Spudbot\Interface\IBindableEvent;
use Spudbot\Types\EventType;

class EventCreated extends IBindableEvent
{

    public function getBoundEvent(): string
    {
        return Event::GUILD_SCHEDULED_EVENT_CREATE;
    }

    public function getListener(): callable
    {
        return function ($event) {
            $eventRepository = $this->spud->getEventRepository();
            $guildRepository = $this->spud->getGuildRepository();

            $guild = $this->discord->guilds->get('id', $event->guild_id);
            if ($guild) {
                $eventPart = $guild->guild_scheduled_events->get('id', $event->guild_scheduled_event_id);
                if ($eventPart) {
                    $guild = $guildRepository->findByPart($guild);

                    try {
                        $eventRepository->findByPart($eventPart);
                    } catch (\OutOfBoundsException $exception) {
                        $event = new \Spudbot\Model\Event();
                        $event->setName($eventPart->name);
                        $event->setGuild($guild);
                        $event->setType(EventType::Native);
                        $event->setNativeId($eventPart->guild_scheduled_event_id);

                        $eventRepository->save($event);
                    }
                }
            }
        };
    }
}