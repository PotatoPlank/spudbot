<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Events\ScheduledEvent;


use Carbon\Carbon;
use DI\Attribute\Inject;
use Discord\WebSockets\Event;
use Spudbot\Events\AbstractEventSubscriber;
use Spudbot\Services\EventAttendanceService;
use Spudbot\Services\EventService;
use Spudbot\Services\GuildService;
use Spudbot\Services\MemberService;

class RemovedUserFromNativeEvent extends AbstractEventSubscriber
{
    public const SESH_BOT_ID = '616754792965865495';
    #[Inject]
    protected GuildService $guildService;
    #[Inject]
    protected EventService $eventService;
    #[Inject]
    protected MemberService $memberService;
    #[Inject]
    protected EventAttendanceService $attendanceService;

    public function getEventName(): string
    {
        return Event::GUILD_SCHEDULED_EVENT_USER_REMOVE;
    }

    public function update($event = null): void
    {
        $guildPart = $this->spud->discord->guilds->get('id', $event->guild_id);
        if (!$guildPart) {
            return;
        }
        $eventPart = $guildPart->guild_scheduled_events->get('id', $event->guild_scheduled_event_id);
        $memberPart = $guildPart->members->get('id', $event->user_id);

        if (!$memberPart || !$eventPart || $eventPart->creator->id === self::SESH_BOT_ID) {
            return;
        }
        $guild = $this->guildService->findOrCreateWithPart($guildPart);
        $output = $guild->getOutputPart($guildPart);

        $eventModel = $this->eventService->findOrCreateNativeWithPart($eventPart);

        $member = $this->memberService->findOrCreateWithPart($memberPart);
        $eventAttendance = $this->attendanceService->findOrCreateByMemberAndEvent($member, $eventModel);
        $eventAttendance->setStatus('No');

        $noShowDateTime = $eventPart->scheduled_start_time->modify($_ENV['EVENT_NO_SHOW_WINDOW']);
        if ($noShowDateTime->lte(Carbon::now())) {
            $eventAttendance->setNoShow(true);
        }

        $this->attendanceService->save($eventAttendance);

        $message = "<@{$member->getDiscordId()}> removed their RSVP to {$eventModel->getName()}";
        $message .= " scheduled at {$eventModel->getScheduledAt()?->format('m/d/Y H:i')}";

        $this->spud->interact()
            ->setTitle('Native Event Attendee Removed')
            ->setDescription($message)
            ->sendTo($output);
    }
}
