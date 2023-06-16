<?php

namespace Spudbot\Bindable\Event\Message;


use Carbon\Carbon;
use Discord\Parts\Channel\Message;
use Discord\WebSockets\Event;
use Spudbot\Interface\IBindableEvent;
use Spudbot\Model\Member;

class ApplyMemberRoleUpgrades extends IBindableEvent
{

    public function getBoundEvent(): string
    {
        return Event::MESSAGE_CREATE;
    }

    public function getListener(): callable
    {
        return function (Message $message){
            if($message->member && !$message->member->user->bot && $message->member->joined_at instanceof Carbon) {
                $builder = $this->spud->getSimpleResponseBuilder();
                $memberRepository = $this->spud->getMemberRepository();
                $guildRepository = $this->spud->getGuildRepository();
                $guild = $guildRepository->findByPart($message->member->guild);
                $output = $message->guild->channels->get('id', $guild->getOutputChannelId());
                if($guild->isOutputLocationThread()){
                    $output = $output->threads->get('id', $guild->getOutputThreadId());
                }

                try{
                    $member = $memberRepository->findByPart($message->member);
                }catch (\Exception $exception){
                    $member = new Member();
                    $member->setGuild($message->guild);
                    $member->setDiscordId($message->member->id);
                    $member->setTotalComments(0);
                    $memberRepository->save($member);
                }

                $levelOneRole = $message->guild->roles->get('id', 1114365923730665481);
                $verificationRole = $message->guild->roles->get('id', 1114365923730665482);
                $memberTenure = $message->member->joined_at->diffInDays(Carbon::now());

                $hasMetMembershipLength = $memberTenure >= 10;
                $hasEnoughComments = $member->getTotalComments() >= 10;
                $isLevelOne = $message->member->roles->isset($levelOneRole->id);
                $isVerified = $message->member->roles->isset($verificationRole->id);

                if(($hasMetMembershipLength && $hasEnoughComments) || $isVerified || $message->member->permissions->moderate_members)
                {
                    if(!$message->member->user->bot && !$isLevelOne){
                        $message->member->addRole($levelOneRole);

                        $builder->setTitle("Member Given {$levelOneRole->name}");
                        $builder->setDescription("{$member->getDiscordId()} met requirements to be given this role.");

                        $output->sendMessage($builder->getEmbeddedMessage());
                    }
                }
            }
        };
    }
}