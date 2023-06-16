<?php

namespace Spudbot\Bindable\Event\Message;


use Discord\Parts\Channel\Message;
use Discord\WebSockets\Event;
use Spudbot\Interface\IBindableEvent;
use Spudbot\Model\Thread;

class LogThreadActivity extends IBindableEvent
{

    public function getBoundEvent(): string
    {
        return Event::MESSAGE_CREATE;
    }

    public function getListener(): callable
    {
        return function (Message $message){
            if($message->thread){
                $guild = $this->spud->getGuildRepository()
                    ->findByPart($message->guild);

                try{
                    $thread = $this->spud->getThreadRepository()
                        ->findByPart($message->thread);
                }catch (\Exception $exception){
                    $thread = new Thread();
                    $thread->setDiscordId($message->thread->id);
                    $thread->setGuild($guild);
                }
//                $this->spud->getThreadRepository()->save($thread);
            }
        };
    }
}