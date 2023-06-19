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
        return function (?Thread $threadPart){
            try{
                $thread = $this->spud->getThreadRepository()->findByPart($threadPart);
                //$this->spud->getThreadRepository()->remove($thread);
            }catch (\Exception $exception){
                /**
                 * Already deleted
                 */
            }
        };
    }
}