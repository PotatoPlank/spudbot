<?php

namespace Spudbot\Bindable\Event\ScheduledEvent;


use Carbon\Carbon;
use Discord\Parts\Channel\Message;
use Discord\Parts\User\Member;
use Discord\WebSockets\Event;
use Spudbot\Helpers\SeshEmbedParser;
use Spudbot\Interface\IBindableEvent;
use Spudbot\Model\EventAttendance;
use Spudbot\Types\EventType;

class AddedUserToSeshEvent extends IBindableEvent
{

    public function getBoundEvent(): string
    {
        return Event::MESSAGE_UPDATE;
    }

    public function getListener(): callable
    {
        return function (Message $message){
            $spud = $this->spud;
            if($message->user_id === '616754792965865495'){
                try{
                    $eventInformation = new SeshEmbedParser($message);

                    $guild = $spud->getGuildRepository()->findByPart($message->guild);
                    $output = $message->guild->channels->get('id', $guild->getOutputChannelId());
                    $messageContent = '';

                    if($guild->isOutputLocationThread()){
                        $output = $output->threads->get('id', $guild->getOutputThreadId());
                    }
                    try{
                        $event = $this->spud->getEventRepository()->findBySeshId($eventInformation->getId());
                        $event->setName($eventInformation->getTitle());
                        $event->setScheduledAt($eventInformation->getScheduledAt());
                    }catch (\Exception $exception){
                        $event = new \Spudbot\Model\Event();
                        $event->setGuild($guild);
                        $event->setName($eventInformation->getTitle());
                        $event->setType(EventType::Sesh);
                        $event->setScheduledAt($eventInformation->getScheduledAt());
                        $event->setSeshId($message->id);
                        $event->setChannelId($message->channel_id);
                    }
                    $spud->getEventRepository()->save($event);

                    $noShowDateTime = Carbon::parse($event->getScheduledAt())->modify($_ENV['EVENT_NO_SHOW_WINDOW'] ?? '-8 hours');
                    $noShowBoolean = $noShowDateTime->lte(Carbon::now());
                    $originalAttendees = [];
                    $currentAttendees = $this->spud->getEventRepository()->getAttendanceByEvent($event);
                    if(!empty($currentAttendees)){
                        /**
                         * @var EventAttendance $attendee
                         */
                        foreach($currentAttendees as $attendee){
                            $originalAttendees[$attendee->getMember()->getDiscordId()] = $attendee;
                        }
                    }
                    foreach($eventInformation->getMembers() as $eventStatus => $attendees)
                    {
                        $statusContentText = '';
                        /**
                         * @var Member $attendee
                         */
                        foreach($attendees as $attendee)
                        {
                            if(!isset($originalAttendees[$attendee->id]) || $eventStatus !== $originalAttendees[$attendee->id]->getStatus()){
                                $member = $this->spud->getMemberRepository()->findByPart($attendee);
                                try{
                                    $eventAttendance = $this->spud->getEventRepository()->getAttendanceByMemberAndEvent($member, $event);
                                }catch (\Exception $exception){
                                    $eventAttendance = new EventAttendance();
                                    $eventAttendance->setEvent($event);
                                    $eventAttendance->setMember($member);
                                    $eventAttendance->setStatus($eventStatus);
                                    $eventAttendance->wasNoShow(false);
                                }
                                if(str_contains($eventStatus, 'No'))
                                {
                                    $eventAttendance->wasNoShow($noShowBoolean);
                                }
                                $this->spud->getMemberRepository()->saveMemberEventAttendance($eventAttendance);
                                $statusContentText .= "<@{$eventAttendance->getMember()->getDiscordId()}>" . PHP_EOL;
                            }
                            unset($originalAttendees[$attendee->id]);
                        }
                        $messageContent .= empty($statusContentText) ? '' :  "{$eventStatus}:" . PHP_EOL . PHP_EOL . $statusContentText;
                    }
                    if(!empty($originalAttendees)){
                        $statusContentText = '';
                        foreach ($originalAttendees as $originalAttendee) {
                            if($originalAttendee->getStatus() !== 'No'){
                                $originalAttendee->wasNoShow($noShowBoolean);
                                $originalAttendee->setStatus('No');
                                $this->spud->getMemberRepository()->saveMemberEventAttendance($originalAttendee);
                                $statusContentText .= "<@{$originalAttendee->getMember()->getDiscordId()}>" . PHP_EOL;
                            }
                        }
                        $messageContent .= empty($statusContentText) ? '' :  'Removed From List:' . PHP_EOL . PHP_EOL . $statusContentText;
                    }

                    if(!empty(trim($messageContent))){
                        $builder = $this->spud->getSimpleResponseBuilder();
                        $builder->setTitle("{$eventInformation->getTitle()} {$eventInformation->getSeshTimeString()}");
                        $builder->setDescription($messageContent);
                        $builder->setAllowedMentions([]);
                        $output->sendMessage($builder->getEmbeddedMessage());
                    }
                }catch (\Exception $exception){
                    /**
                     * Not a sesh embed
                     */
                }
            }
        };
    }
}