<?php

namespace Spudbot\Bindable\Event\ScheduledEvent;


use Carbon\Carbon;
use Discord\WebSockets\Event;
use Spudbot\Interface\IBindableEvent;
use Spudbot\Model\EventAttendance;
use Spudbot\Types\EventType;

class RemovedUserFromNativeEvent extends IBindableEvent
{

    public function getBoundEvent(): string
    {
        return Event::GUILD_SCHEDULED_EVENT_USER_REMOVE;
    }

    public function getListener(): callable
    {
        return function ($event){
            $guildRepository = $this->spud->getGuildRepository();
            $eventRepository = $this->spud->getEventRepository();
            $memberRepository = $this->spud->getMemberRepository();
            $builder = $this->spud->getSimpleResponseBuilder();
            $guildPart = $this->discord->guilds->get('id', $event->guild_id);
            $eventPart = $guildPart->guild_scheduled_events->get('id', $event->guild_scheduled_event_id);

            if($eventPart && $eventPart->creator->id !== '616754792965865495'){
                $guild = $guildRepository->findByPart($guildPart);
                try{
                    $eventModel = $eventRepository->findByPart($eventPart);
                }catch(\OutOfBoundsException $exception){

                    $eventModel = new \Spudbot\Model\Event();
                    $eventModel->setNativeId($eventPart->id);
                    $eventModel->setType(EventType::Native);
                    $eventModel->setGuild($guild);
                    $eventModel->setName($eventPart->name);
                    $eventModel->setScheduledAt($eventPart->scheduled_start_time);

                    $eventRepository->save($eventModel);
                }
                $output = $guildPart->channels->get('id', $guild->getOutputChannelId());
                if($guild->isOutputLocationThread()){
                    $output = $output->threads->get('id', $guild->getOutputThreadId());
                }

                $memberPart = $guildPart->members->get('id', $event->user_id);
                $member = $memberRepository->findByPart($memberPart);
                try{
                    $eventAttendance = $eventRepository->getAttendanceByMemberAndEvent($member, $eventModel);
                    $eventAttendance->setStatus('No');
                }catch (\Exception $exception){
                    $eventAttendance = new EventAttendance();
                    $eventAttendance->setEvent($eventModel);
                    $eventAttendance->setMember($member);
                    $eventAttendance->setStatus('No');
                }

                $noShowDateTime = $eventPart->scheduled_start_time->modify($_ENV['EVENT_NO_SHOW_WINDOW']);
                if($noShowDateTime->lte(Carbon::now()))
                {
                    $eventAttendance->wasNoShow(true);
                }

                $memberRepository->saveMemberEventAttendance($eventAttendance);

                $builder->setTitle('Native Event Attendee Removed');
                $builder->setDescription("<@{$member->getDiscordId()}> removed their RSVP to {$eventModel->getName()} scheduled at {$eventModel->getScheduledAt()->format('m/d/Y H:i')}");
                $output->sendMessage($builder->getEmbeddedMessage());
            }
        };
    }
}