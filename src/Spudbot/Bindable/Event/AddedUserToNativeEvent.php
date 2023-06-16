<?php

namespace Spudbot\Bindable\Event;


use Discord\WebSockets\Event;
use Spudbot\Model\EventAttendance;
use Spudbot\Types\EventType;

class AddedUserToNativeEvent extends BindableEvent
{

    public function getBoundEvent(): string
    {
        return Event::GUILD_SCHEDULED_EVENT_USER_ADD;
    }

    public function getListener(): callable
    {
        return function ($event){
            $guildRepository = $this->spud->getGuildRepository();
            $eventRepository = $this->spud->getEventRepository();
            $memberRepository = $this->spud->getMemberRepository();
            $builder = $this->spud->getSimpleResponseBuilder();
            $guildPart = $this->discord->guilds->get('id', $event->guild_id);
            $output = $guildPart->channels->get('id', $guild->getOutputChannelId());
            if($guild->isOutputLocationThread()){
                $output = $output->threads->get('id', $guild->getOutputThreadId());
            }
            $eventPart = $guildPart->guild_scheduled_events->get('id', $event->guild_scheduled_event_id);

            if($eventPart && $eventPart->creator->id !== '616754792965865495'){
                try{
                    $eventModel = $eventRepository->findByPart($eventPart);
                }catch(\Exception $exception){
                    $guild = $guildRepository->findByPart($guildPart);

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
                try{
                    $eventAttendance = $eventRepository->getAttendanceByMemberAndEvent($member, $eventModel);
                    $eventAttendance->setStatus('Attendees');
                }catch (\Exception $exception){
                    $eventAttendance = new EventAttendance();
                    $eventAttendance->setEvent($eventModel);
                    $eventAttendance->setMember($member);
                    $eventAttendance->setStatus('Attendees');
                }
                /**
                 * TODO save event attendance
                 */

                $builder->setTitle('Native Event Attendee');
                $builder->setDescription("<@{$member->getDiscordId()}> marked they were interested in {$eventModel->getName()} scheduled at {$eventModel->getScheduledAt()->format('m/d/Y H:i')}");
                $output->sendMessage($builder->getEmbeddedMessage());
            }
        };
    }
}