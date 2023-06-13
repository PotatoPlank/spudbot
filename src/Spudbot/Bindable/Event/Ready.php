<?php

namespace Spudbot\Bindable\Event;


use Discord\WebSockets\Event;

class Ready extends BindableEvent
{

    public function getBoundEvent(): string
    {
        return Event::MESSAGE_CREATE;
    }

    public function getListener(): callable
    {
        return function (){
            foreach($this->discord->guilds as $guild){
                var_dump($this->discord->application->owner);exit;
            }
            exit;
        };
    }
}