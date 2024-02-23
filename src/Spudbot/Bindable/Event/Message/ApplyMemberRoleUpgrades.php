<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023-2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Bindable\Event\Message;


use Carbon\Carbon;
use Discord\Parts\Channel\Message;
use Discord\Parts\Guild\Role;
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
        return function (Message $message) {
            if ($message->member && !$message->member->user->bot && $message->member->joined_at instanceof Carbon && $message->guild_id == '1114365923625816155') {
                $this->discord->getLogger()
                    ->info("Checking to upgrade the membership of {$message->member->displayname}");
                $memberRepository = $this->spud->memberRepository;
                $guildRepository = $this->spud->guildRepository;
                $guild = $guildRepository->findByPart($message->member->guild);
                $output = $message->guild->channels->get('id', $guild->getOutputChannelId());
                if ($guild->isOutputLocationThread()) {
                    $output = $output->threads->get('id', $guild->getOutputThreadId());
                }

                $username = $message->member->nick ?? $message->member->displayname;

                try {
                    $member = $memberRepository->findByPart($message->member);
                } catch (\Exception $exception) {
                    $member = new Member();
                    $member->setGuild($guild);
                    $member->setDiscordId($message->member->id);
                    $member->setTotalComments(0);
                    $member->setUsername($username);
                    $memberRepository->save($member);
                }

                $memberTenure = $message->member->joined_at->diffInDays(Carbon::now());

                $hasMetMembershipLength = $memberTenure >= 10;
                $hasEnoughComments = $member->getTotalComments() >= 10;
                $isLevelOne = $message->member->roles->isset(1114365923730665481);
                $isVerified = $message->member->roles->isset(1114365923730665482);

                if (($hasMetMembershipLength && $hasEnoughComments) || $isVerified || $message->member->getPermissions(
                    )->moderate_members) {
                    if (!$message->member->user->bot && !$isLevelOne) {
                        $message->member->addRole(1114365923730665481);

                        $message->guild->roles->fetch(1114365923730665481)->done(
                            function (Role $role) use ($member, $output) {
                                $builder = $this->spud->getSimpleResponseBuilder();
                                $builder->setTitle("Member Given {$role->name}");
                                $builder->setDescription(
                                    "<@{$member->getDiscordId()}> met requirements to be given this role."
                                );
                                $output->sendMessage($builder->getEmbeddedMessage());
                            }
                        );
                    }
                }
            }
        };
    }
}
