<?php

namespace Spudbot\Bindable\Event;


use Carbon\Carbon;
use Discord\Parts\Channel\Message;
use Discord\Parts\Guild\Ban;
use Discord\WebSockets\Event;

class MemberBanned extends BindableEvent
{

    public function getBoundEvent(): string
    {
        return Event::GUILD_BAN_ADD;
    }

    public function getListener(): callable
    {
        return function (Ban $ban){
            $builder = $this->spud->getSimpleResponseBuilder();
            $publicModLogChannel = $ban->guild->channels->get('id', 1114365924733104133);

            $context = [
                'username' => $ban->user->username,
                'reason' => $ban->reason,
                'timestamp' => Carbon::now()->timestamp,
            ];
            $message = $this->spud->getTwig()->render('ban_alert.twig', $context);

            $builder->setTitle('Member Banned');
            $builder->setDescription($message);

            $publicModLogChannel->sendMessage($builder->getEmbeddedMessage());
        };
    }
}