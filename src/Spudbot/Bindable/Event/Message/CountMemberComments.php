<?php

namespace Spudbot\Bindable\Event\Message;


use Discord\Parts\Channel\Message;
use Discord\WebSockets\Event;
use Spudbot\Interface\IBindableEvent;
use Spudbot\Model\Member;

class CountMemberComments extends IBindableEvent
{

    public function getBoundEvent(): string
    {
        return Event::MESSAGE_CREATE;
    }

    public function getListener(): callable
    {
        return function (Message $message){
            if($message->member && !$message->member->user->bot){
                $memberRepository = $this->spud->getMemberRepository();


                try{
                    $member = $memberRepository->findByPart($message->member);
                    $member->setTotalComments($member->getTotalComments() + 1);
                }catch(\Exception){
                    $member = new Member();
                    $member->setGuild($message->guild);
                    $member->setDiscordId($message->member->id);
                    $member->setTotalComments(1);
                }
                $memberRepository->save($member);
            }
        };
    }
}