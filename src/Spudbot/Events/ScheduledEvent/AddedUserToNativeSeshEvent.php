<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Events\ScheduledEvent;


use Discord\Parts\User\Member;
use Discord\WebSockets\Event;
use Spudbot\Interface\AbstractEventSubscriber;

class AddedUserToNativeSeshEvent extends AbstractEventSubscriber
{
    public const SESH_BOT_ID = '616754792965865495';

    public function getEventName(): string
    {
        return Event::GUILD_SCHEDULED_EVENT_USER_ADD;
    }

    public function update($event = null): void
    {
        $guildRepository = $this->spud->guildRepository;
        $builder = $this->spud->getSimpleResponseBuilder();
        $builder->setTitle('Native Event RSVP Notification');

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

        $guild = $guildRepository->findByPart($guildPart);
        $output = $guildPart->channels->get('id', $guild->getOutputChannelId());
        if ($guild->isOutputLocationThread()) {
            $output = $output->threads->get('id', $guild->getOutputThreadId());
        }

        if (!$output) {
            return;
        }

        $message = "<@{$event->user_id}> was sent a DM with the link to the sesh event for {$eventPart->name}.";
        $builder->setDescription($message);
        $output->sendMessage($builder->getEmbeddedMessage());

        $guildPart->members->fetch($event->user_id)->done(function (Member $member) use ($eventPart) {
            $context = [
                'username' => $member->user->username,
                'eventName' => $eventPart->name,
                'guildName' => $member->guild->name,
                'eventUrl' => $eventPart->entity_metadata->location,
            ];
            $message = $this->spud->twig->render('dm/native_event.twig', $context);
            $member->sendMessage($message);
        });
    }
}
