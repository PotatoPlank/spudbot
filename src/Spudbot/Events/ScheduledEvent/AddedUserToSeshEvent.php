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
use Exception;
use Spudbot\Events\AbstractEventSubscriber;
use Spudbot\Helpers\SeshEmbedParser;
use Spudbot\Model\EventAttendance;
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

    public function update(?Message $message = null): void
    {
        if (!$message || $message->user_id !== self::SESH_BOT_ID) {
            return;
        }
        $messageContent = '';
        $originalAttendees = [];
        try {
            $eventInformation = new SeshEmbedParser($message);

            $guild = $this->guildService->findOrCreateWithPart($message->guild);
            $output = $guild->getOutputPart($message->guild);

            $event = $this->eventService->findOrCreateSesh($eventInformation->getId(), [
                'seshMessageId' => $message->id,
                'channelId' => $message->channel_id,
                'type' => EventType::Sesh,
                'guild' => $guild,
                'name' => $eventInformation->getTitle(),
                'scheduledAt' => $eventInformation->getScheduledAt(),
            ]);

            $noShowDateTime = Carbon::parse($event->getScheduledAt())
                ->modify($_ENV['EVENT_NO_SHOW_WINDOW'] ?? '-8 hours');
            $noShowBoolean = $noShowDateTime->lte(Carbon::now());

            $currentAttendees = $this->eventService->getAttendance($event);
            if (!$currentAttendees->empty()) {
                /**
                 * @var EventAttendance $attendee
                 */
                foreach ($currentAttendees as $attendee) {
                    $originalAttendees[$attendee->getMember()->getDiscordId()] = $attendee;
                }
            }
            foreach ($eventInformation->getMembers() as $eventStatus => $attendees) {
                $statusContentText = '';
                /**
                 * @var Member $attendee
                 */
                foreach ($attendees as $attendee) {
                    $id = $attendee->id;
                    $shouldAdd = !isset($originalAttendees[$id]) ||
                        $eventStatus !== $originalAttendees[$id]->getStatus();

                    if ($shouldAdd) {
                        $member = $this->memberService->findOrCreateWithPart($attendee);
                        $eventAttendance = $this->attendanceService->findOrCreateByMemberAndEvent($member, $event);
                        $eventAttendance->setNoShow(false);
                        if (str_contains($eventStatus, 'No')) {
                            $eventAttendance->setNoShow($noShowBoolean);
                        }
                        $this->attendanceService->save($eventAttendance);
                        $statusContentText .= "<@{$eventAttendance->getMember()->getDiscordId()}>" . PHP_EOL;
                    }
                    unset($originalAttendees[$id]);
                }
                if (!empty($statusContentText)) {
                    $messageContent .= "{$eventStatus}:" . PHP_EOL . PHP_EOL . $statusContentText;
                }
            }
            if (!empty($originalAttendees)) {
                $statusContentText = '';
                foreach ($originalAttendees as $originalAttendee) {
                    if ($originalAttendee->getStatus() !== 'No') {
                        $originalAttendee->setNoShow($noShowBoolean);
                        $originalAttendee->setStatus('No');
                        $this->attendanceService->save($originalAttendee);
                        $statusContentText .= "<@{$originalAttendee->getMember()->getDiscordId()}>" . PHP_EOL;
                    }
                }
                if (!empty($statusContentText)) {
                    $messageContent .= 'Removed From List:' . PHP_EOL . PHP_EOL . $statusContentText;
                }
            }
            $messageContent = trim($messageContent);
            if (!empty($messageContent)) {
                $this->spud->interact()
                    ->setTitle("{$eventInformation->getTitle()} {$eventInformation->getSeshTimeString()}")
                    ->setDescription($messageContent)
                    ->setAllowedMentions([])
                    ->sendTo($output);
            }
        } catch (Exception $exception) {
            /**
             * Not a sesh embed
             */
        }
    }
}
