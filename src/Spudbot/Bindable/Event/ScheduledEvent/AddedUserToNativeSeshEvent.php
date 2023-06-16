<?php

namespace Spudbot\Bindable\Event\ScheduledEvents;


use Discord\Parts\User\Member;
use Discord\WebSockets\Event;
use Spudbot\Interface\IBindableEvent;

class AddedUserToNativeSeshEvent extends IBindableEvent
{

    public function getBoundEvent(): string
    {
        return Event::GUILD_SCHEDULED_EVENT_USER_ADD;
    }

    public function getListener(): callable
    {
        return function ($event)
        {
            $guildRepository = $this->spud->getGuildRepository();
            $builder = $this->spud->getSimpleResponseBuilder();
            $builder->setTitle('Native Event RSVP Notification');
            $guildPart = $this->discord->guilds->get('id', $event->guild_id);
            $eventPart = $guildPart->guild_scheduled_events->get('id', $event->guild_scheduled_event_id);

            if($eventPart && $eventPart->creator->id === '616754792965865495')
            {
                $this->discord->getLogger()->info("A user attempted to RSVP to a native event instead of the Sesh event.");

                $guild = $guildRepository->findByPart($guildPart);
                $output = $guildPart->channels->get('id', $guild->getOutputChannelId());
                if($guild->isOutputLocationThread()){
                    $output = $output->threads->get('id', $guild->getOutputThreadId());
                }

                $message = "<@{$event->user_id}> was sent a DM with the link to the sesh event for {$eventPart->name}.";
                $builder->setDescription($message);
                $output->sendMessage($builder->getEmbeddedMessage());

                $guildPart->members->fetch($event->user_id)->done(function (Member $member) use ($eventPart){
                    $context = [
                        'username' => $member->user->username,
                        'eventName' => $eventPart->name,
                        'guildName' => $member->guild->name,
                        'eventUrl' => $eventPart->entity_metadata->location,
                    ];
                    $message = $this->spud->getTwig()->render('dm/native_event.twig', $context);
                    $member->sendMessage($message);
                });
            }
        };
    }
}