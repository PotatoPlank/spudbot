<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Events\ScheduledEvent;


use Carbon\Carbon;
use DI\Attribute\Inject;
use Discord\Parts\Channel\Message;
use Discord\Parts\User\Member;
use Discord\WebSockets\Event;
use Spudbot\Events\AbstractEventSubscriber;
use Spudbot\Exception\InvalidSeshEmbed;
use Spudbot\Model\EventAttendance;
use Spudbot\Parsers\Sesh\SeshParser;
use Spudbot\Services\EventAttendanceService;
use Spudbot\Services\EventService;
use Spudbot\Services\GuildService;
use Spudbot\Services\MemberService;
use Spudbot\Types\EventType;

class AddedUserToSeshEvent extends AbstractEventSubscriber
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
        return Event::MESSAGE_UPDATE;
    }

    public function update(?object $message = null): void
    {
        if (!$message instanceof Message) {
            return;
        }
        $messageContent = '';
        try {
            $seshEmbed = SeshParser::fromMessage($message);
            $guild = $this->guildService->findOrCreateWithPart($message->guild);
            $output = $guild->getOutputPart($message->guild);

            $event = $this->eventService->findOrCreateSesh($seshEmbed->id, [
                'sesh_message_id' => $message->id,
                'discord_channel_id' => $message->channel_id,
                'type' => EventType::Sesh,
                'guild' => $guild,
                'name' => $seshEmbed->title,
                'scheduled_at' => $seshEmbed->scheduledAt,
                'native_event_id' => null,
            ]);

            if (!$event) {
                return;
            }
            $event->setScheduledAt($seshEmbed->scheduledAt);
            $event->setName($seshEmbed->title);

            $noShowDateTime = $event->getScheduledAt()?->modify($_ENV['EVENT_NO_SHOW_WINDOW'] ?? '-8 hours');
            if (!$noShowDateTime) {
                return;
            }
            $noShowBoolean = $noShowDateTime->lte(Carbon::now());

            $currentAttendees = $this->eventService->getAttendance($event);
            $originalAttendees = collect();
            if ($currentAttendees->isNotEmpty()) {
                /**
                 * @var EventAttendance $attendee
                 */
                foreach ($currentAttendees as $attendee) {
                    $originalAttendees->put($attendee->getMember()->getDiscordId(), $attendee);
                }
            }
            foreach ($seshEmbed->members as $eventStatus => $attendees) {
                $statusContentText = '';
                /**
                 * @var Member $attendee
                 */
                foreach ($attendees as $attendee) {
                    $shouldUpdate = !$originalAttendees->has($attendee->id) ||
                        $eventStatus !== $originalAttendees->get($attendee->id)->getStatus();

                    if ($shouldUpdate) {
                        $member = $this->memberService->findOrCreateWithPart($attendee);
                        $eventAttendance = $this->attendanceService->findOrCreateByMemberAndEvent($member, $event);
                        $eventAttendance->setStatus($eventStatus);
                        $eventAttendance->setNoShow(str_contains($eventStatus, 'No'));
                        $this->attendanceService->save($eventAttendance);
                        $statusContentText .= "<@{$member->getDiscordId()}>" . PHP_EOL;
                    }
                    $originalAttendees->forget($attendee->id);
                }
                if (!empty($statusContentText)) {
                    $messageContent .= "{$eventStatus}:" . PHP_EOL . PHP_EOL . $statusContentText;
                }
            }

            if ($originalAttendees->isNotEmpty()) {
                $statusContentText = '';
                foreach ($originalAttendees as $originalAttendee) {
                    if ($originalAttendee->getStatus() === 'No') {
                        continue;
                    }
                    $originalAttendee->setNoShow($noShowBoolean);
                    $originalAttendee->setStatus('No');
                    $this->attendanceService->save($originalAttendee);
                    $statusContentText .= "<@{$originalAttendee->getMember()->getDiscordId()}>" . PHP_EOL;
                }
                if (!empty($statusContentText)) {
                    $messageContent .= 'Removed From List:' . PHP_EOL . PHP_EOL . $statusContentText;
                }
            }
            $messageContent = trim($messageContent);
            if (!empty($messageContent)) {
                $this->spud->interact()
                    ->setTitle("{$seshEmbed->title} {$seshEmbed->seshTimeString}")
                    ->setDescription($messageContent)
                    ->setAllowedMentions([])
                    ->sendTo($output);
            }
        } catch (InvalidSeshEmbed $exception) {
            /**
             * Not a sesh embed
             */
        }
    }

    public function canRun(?object $message = null): bool
    {
        return $message instanceof Message && $message->user_id === self::SESH_BOT_ID;
    }
}
