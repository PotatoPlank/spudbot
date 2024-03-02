<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Events\ScheduledEvent;


use DI\Attribute\Inject;
use Discord\WebSockets\Event;
use Spudbot\Interface\AbstractEventSubscriber;
use Spudbot\Services\EventAttendanceService;
use Spudbot\Services\EventService;
use Spudbot\Services\GuildService;
use Spudbot\Services\MemberService;

class AddedUserToNativeEvent extends AbstractEventSubscriber
{
    public const SESH_BOT_ID = '616754792965865495';
    #[Inject]
    protected GuildService $guildService;
    #[Inject]
    protected MemberService $memberService;
    #[Inject]
    protected EventService $eventService;
    #[Inject]
    protected EventAttendanceService $attendanceService;

    public function getEventName(): string
    {
        return Event::GUILD_SCHEDULED_EVENT_USER_ADD;
    }

    public function update($event = null): void
    {
        if (!$event) {
            return;
        }
        $guildPart = $this->spud->discord->guilds->get('id', $event->guild_id);
        if (!$guildPart) {
            return;
        }
        $eventPart = $guildPart->guild_scheduled_events->get('id', $event->guild_scheduled_event_id);
        $memberPart = $guildPart->members->get('id', $event->user_id);

        if (!$eventPart || !$memberPart || $eventPart->creator->id === self::SESH_BOT_ID) {
            return;
        }

        $guild = $this->guildService->findOrCreateWithPart($guildPart);
        $output = $guild->getOutputPart($guildPart);
        $eventModel = $this->eventService->findOrCreateNativeWithPart($eventPart);
        $member = $this->memberService->findOrCreateWithPart($memberPart);
        $this->attendanceService->findOrCreateByMemberAndEvent($member, $eventModel);

        $message = "<@{$member->getDiscordId()}> marked they were interested in {$eventModel->getName()}";
        $message .= " scheduled at {$eventModel->getScheduledAt()->format('m/d/Y H:i')}";

        $this->spud->interact()
            ->setTitle('Native Event Attendee')
            ->setDescription($message)
            ->sendTo($output);
    }
}
