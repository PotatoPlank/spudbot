<?php

namespace Spudbot\Bindable\Event\Message;


use Discord\Parts\Channel\Message;
use Discord\WebSockets\Event;
use Spudbot\Interface\IBindableEvent;

class BotMentioned extends IBindableEvent
{

    public function getBoundEvent(): string
    {
        return Event::MESSAGE_CREATE;
    }

    public function getListener(): callable
    {
        return function (Message $message){
            $botMentions = $message->mentions->get('id', $this->discord->application->id);
            if($botMentions){
                $message->react(':grittywhat:1115440446114640013');
            }
        };
    }
}