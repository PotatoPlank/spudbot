<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Events\ScheduledEvent;


use Discord\WebSockets\Event;
use Spudbot\Interface\AbstractEventSubscriber;
use Spudbot\Model\EventAttendance;
use Spudbot\Types\EventType;

class AddedUserToNativeEvent extends AbstractEventSubscriber
{
    public const SESH_BOT_ID = '616754792965865495';

    public function getEventName(): string
    {
        return Event::GUILD_SCHEDULED_EVENT_USER_ADD;
    }

    public function update($event = null): void
    {
        $guildRepository = $this->spud->guildRepository;
        $eventRepository = $this->spud->eventRepository;
        $memberRepository = $this->spud->memberRepository;
        $builder = $this->spud->getSimpleResponseBuilder();

        $guildPart = $this->spud->discord->guilds->get('id', $event->guild_id);
        if (!$guildPart) {
            return;
        }
        $guild = $guildRepository->findByPart($guildPart);

        $output = $guildPart->channels->get('id', $guild->getOutputChannelId());
        if ($guild->isOutputLocationThread()) {
            $output = $output->threads->get('id', $guild->getOutputThreadId());
        }
        if (!$output) {
            return;
        }
        $eventPart = $guildPart->guild_scheduled_events->get('id', $event->guild_scheduled_event_id);

        if (!$eventPart || $eventPart->creator->id === self::SESH_BOT_ID) {
            return;
        }

        try {
            $eventModel = $eventRepository->findByPart($eventPart);
        } catch (\OutOfBoundsException $exception) {
            $eventModel = new \Spudbot\Model\Event();
            $eventModel->setNativeId($eventPart->id);
            $eventModel->setType(EventType::Native);
            $eventModel->setGuild($guild);
            $eventModel->setName($eventPart->name);
            $eventModel->setScheduledAt($eventPart->scheduled_start_time);

            $eventRepository->save($eventModel);
        }

        $memberPart = $guildPart->members->get('id', $event->user_id);
        $member = $memberRepository->findByPart($memberPart);
        try {
            $eventAttendance = $eventRepository->getAttendanceByMemberAndEvent($member, $eventModel);
            $eventAttendance->setStatus('Attendees');
        } catch (\OutOfBoundsException $exception) {
            $eventAttendance = new EventAttendance();
            $eventAttendance->setEvent($eventModel);
            $eventAttendance->setMember($member);
            $eventAttendance->setStatus('Attendees');
        }

        $memberRepository->saveMemberEventAttendance($eventAttendance);

        $builder->setTitle('Native Event Attendee');
        $builder->setDescription(
            "<@{$member->getDiscordId()}> marked they were interested in {$eventModel->getName()} scheduled at {$eventModel->getScheduledAt()->format('m/d/Y H:i')}"
        );
        $output->sendMessage($builder->getEmbeddedMessage());
    }
}
