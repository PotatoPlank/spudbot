<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023-2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Events\Message;


use Carbon\Carbon;
use Discord\Parts\Channel\Message;
use Discord\Parts\Guild\Role;
use Discord\WebSockets\Event;
use Spudbot\Interface\AbstractEventSubscriber;
use Spudbot\Model\Member;

class ApplyMemberRoleUpgrades extends AbstractEventSubscriber
{
    private const APPLIES_TO_GUILD = '1114365923625816155';
    private const LEVEL_ONE_ROLE = 1114365923730665481;
    private const VERIFIED_ROLE = 1114365923730665482;
    private const MEMBER_TENURE_MINIMUM = 10;
    private const MEMBER_COMMENTS_MINIMUM = 10;

    public function getEventName(): string
    {
        return Event::MESSAGE_CREATE;
    }

    public function update(?Message $message = null): void
    {
        if (!$message) {
            return;
        }
        if (!$message->member || $message->member->user->bot || $message->guild_id !== self::APPLIES_TO_GUILD) {
            return;
        }
        if (!$message->member->joined_at instanceof Carbon) {
            return;
        }
        $memberRepository = $this->spud->memberRepository;
        $guildRepository = $this->spud->guildRepository;

        $this->spud->discord->getLogger()
            ->info("Checking to upgrade the membership of {$message->member->displayname}");

        $guild = $guildRepository->findByPart($message->member->guild);
        $output = $message->guild->channels->get('id', $guild->getOutputChannelId());
        if ($guild->isOutputLocationThread()) {
            $output = $output->threads->get('id', $guild->getOutputThreadId());
        }

        if (!$output) {
            return;
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

        $hasMetMembershipLength = $memberTenure >= self::MEMBER_TENURE_MINIMUM;
        $hasEnoughComments = $member->getTotalComments() >= self::MEMBER_COMMENTS_MINIMUM;
        $isLevelOne = $message->member->roles->isset(self::LEVEL_ONE_ROLE);
        $isVerified = $message->member->roles->isset(self::VERIFIED_ROLE);
        $canModerateMembers = $message->member->getPermissions()->moderate_members;

        if ((!$hasMetMembershipLength || !$hasEnoughComments) && !$canModerateMembers && !$isVerified) {
            return;
        }
        if ($message->member->user->bot || $isLevelOne) {
            return;
        }

        $message->member->addRole(self::LEVEL_ONE_ROLE);

        $message->guild->roles->fetch(self::LEVEL_ONE_ROLE)->done(
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
