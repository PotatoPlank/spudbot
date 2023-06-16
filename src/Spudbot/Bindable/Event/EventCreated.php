<?php

namespace Spudbot\Bindable\Event;


use Discord\WebSockets\Event;
use Spudbot\Types\EventType;

class EventCreated extends BindableEvent
{

    public function getBoundEvent(): string
    {
        return Event::GUILD_SCHEDULED_EVENT_CREATE;
    }

    public function getListener(): callable
    {
        return function ($event){
            $eventRepository = $this->spud->getEventRepository();
            $guildRepository = $this->spud->getGuildRepository();

            $guild = $this->discord->guilds->get('id', $event->guild_id);
            $eventPart = $guild->guild_scheduled_events->get('id', $event->guild_scheduled_event_id);

            $guild = $guildRepository->findByPart($eventPart->guild);

            try {
                $eventRepository->findByPart($eventPart);
            }catch(\Exception $exception){
                $event = new \Spudbot\Model\Event();
                $event->setName($eventPart->name);
                $event->setGuild($guild);
                $event->setType(EventType::Native);
                $event->setNativeId($event->guild_scheduled_event_id);

                $eventRepository->save($event);
            }
        };
    }
}