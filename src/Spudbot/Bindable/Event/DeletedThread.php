<?php

namespace Spudbot\Bindable\Event;



use Discord\Parts\Thread\Thread;
use Discord\WebSockets\Event;

class DeletedThread extends BindableEvent
{

    public function getBoundEvent(): string
    {
        return Event::THREAD_DELETE;
    }

    public function getListener(): callable
    {
        return function (?Thread $thread){
            try{
                $threadModel = $this->spud->getThreadRepository()->findByPart($thread);
                /**
                 * TODO Delete thread
                 */
            }catch (\Exception $exception){
                /**
                 * Already deleted
                 */
            }
        };
    }
}