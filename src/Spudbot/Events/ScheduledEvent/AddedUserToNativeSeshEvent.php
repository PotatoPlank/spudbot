<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Events\ScheduledEvent;


use DI\Attribute\Inject;
use Discord\Parts\User\Member;
use Discord\WebSockets\Event;
use Spudbot\Interface\AbstractEventSubscriber;
use Spudbot\Services\GuildService;

class AddedUserToNativeSeshEvent extends AbstractEventSubscriber
{
    public const SESH_BOT_ID = '616754792965865495';
    #[Inject]
    protected GuildService $guildService;

    public function getEventName(): string
    {
        return Event::GUILD_SCHEDULED_EVENT_USER_ADD;
    }

    public function update($event = null): void
    {
        $guildPart = $this->spud->discord->guilds->get('id', $event->guild_id);
        if (!$guildPart) {
            return;
        }
        $eventPart = $guildPart->guild_scheduled_events->get('id', $event->guild_scheduled_event_id);

        if (!$eventPart || $eventPart->creator->id !== self::SESH_BOT_ID) {
            return;
        }

        $this->spud->discord->getLogger()->info(
            "A user attempted to RSVP to a native event instead of the Sesh event."
        );

        $guild = $this->guildService->findWithPart($guildPart);
        $output = $guild->getOutputPart($guildPart);

        $message = "<@{$event->user_id}> was sent a DM with the link to the sesh event for {$eventPart->name}.";
        $this->spud->interact()
            ->setTitle('Native Event RSVP Notification')
            ->setDescription($message)
            ->sendTo($output);

        $guildPart->members->fetch($event->user_id)
            ->done(function (Member $member) use ($eventPart) {
                $message = $this->spud->twig->render('dm/native_event.twig', [
                    'username' => $member->user->username,
                    'eventName' => $eventPart->name,
                    'guildName' => $member->guild->name,
                    'eventUrl' => $eventPart->entity_metadata->location,
                ]);
                $member->sendMessage($message);
            });
    }
}
