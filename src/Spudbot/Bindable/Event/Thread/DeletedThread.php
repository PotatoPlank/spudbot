<?php

namespace Spudbot\Bindable\Event\Thread;



use Discord\Parts\Thread\Thread;
use Discord\WebSockets\Event;
use Spudbot\Interface\IBindableEvent;

class DeletedThread extends IBindableEvent
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