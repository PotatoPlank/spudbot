<?php

namespace Spudbot\Bindable\Event;


use Carbon\Carbon;
use Discord\Parts\Channel\Channel;
use Discord\Parts\User\Member;
use Discord\WebSockets\Event;

class MemberLeaves extends BindableEvent
{

    public function getBoundEvent(): string
    {
        return Event::GUILD_MEMBER_REMOVE;
    }

    public function getListener(): callable
    {
        return function (Member $member){
            $memberCount = $member->guild->member_count;
            $categoryName = 'Member Count 📈';
            /**
             * Todo support in setup
             */
            $category = $member->guild->channels->get('name', $categoryName);
            if(!$category)
            {
                $category = new Channel($this->discord);
                $category->type = Channel::TYPE_CATEGORY;
                $category->name = $categoryName;
                $member->guild->channels->save($category);
            }
            $channel = $member->guild->channels->get('parent_id', $category->id);
            if(!$channel)
            {
                $everyoneRole = $member->guild->roles->get('name', '@everyone');
                $channel = new Channel($this->discord);
                $channel->type = Channel::TYPE_VOICE;
                $channel->name = "Member Count: {$memberCount}";
                $channel->setPermissions($everyoneRole, [
                    'view_channel',
                ], [
                    'connect',
                ]);
                $channel->parent_id = $category->id;
            }else{
                $channel->name = "Member Count: {$memberCount}";
            }

            $member->guild->channels->save($channel);
        };
    }
}