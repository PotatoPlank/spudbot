<?php

namespace Spudbot\Bindable\Event\ScheduledEvents;


use Discord\Parts\Channel\Message;
use Discord\WebSockets\Event;
use Spudbot\Helpers\SeshEmbedParser;
use Spudbot\Interface\IBindableEvent;
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
                    if($guild->isOutputLocationThread()){
                        $output = $output->threads->get('id', $guild->getOutputThreadId());
                    }
                    try{
                        /**
                         * TODO: Add ability to detect by sesh id
                         */
                        $boo = 1;
                    }catch (\Exception $exception){
                        $event = new \Spudbot\Model\Event();
                        $event->setGuild($guild);
                        $event->setName($eventInformation->getTitle());
                        $event->setType(EventType::Sesh);
                        $event->setScheduledAt($eventInformation->getScheduledAt());
                        $event->setSeshId($message->id);
                        $event->setChannelId($message->channel_id);
                    }
//                    $spud->getEventRepository()->save($event);
                    /**
                     * TODO: Finish updating attendance
                     */
                }catch (\Exception $exception){
                    /**
                     * Not a sesh embed
                     */
                }
            }
        };
    }
}