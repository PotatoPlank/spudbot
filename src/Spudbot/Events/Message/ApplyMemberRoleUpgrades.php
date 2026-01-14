<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023-2026. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Events\Message;


use Carbon\Carbon;
use DI\Attribute\Inject;
use Discord\Parts\Channel\Message;
use Discord\Parts\Guild\Role;
use Discord\WebSockets\Event;
use Exception;
use Spudbot\Events\AbstractEventSubscriber;
use Spudbot\Model\Guild;
use Spudbot\Services\GuildService;
use Spudbot\Services\MemberService;

class ApplyMemberRoleUpgrades extends AbstractEventSubscriber
{
    private const APPLIES_TO_GUILD = '1114365923625816155';
    private const MEMBER_TENURE_MINIMUM = 10;
    private const MEMBER_COMMENTS_MINIMUM = 10;
    #[Inject]
    protected GuildService $guildService;
    #[Inject]
    protected MemberService $memberService;

    public function getEventName(): string
    {
        return Event::MESSAGE_CREATE;
    }

    public function update(?Message $message = null): void
    {
        if (!$message || !$message->member) {
            return;
        }

        $this->spud->logger->notice('Checking eligibility to upgrade user membership.', [$message->member->displayname]);

        $guild = $this->guildService->findOrCreateWithPart($message->member->guild);
        $verifiedRole = $guild->verifiedMembersRoleId;
        $tenuredRoleId = $guild->tenuredMemberRoleId;

        try {
            $output = $guild->getChannelThreadPart(Guild::BOT_LOG_CHANNEL, $message->guild);
        } catch (Exception $exception) {
            $this->spud->logger
                ->error($exception->getMessage());
            return;
        }

        $member = $this->memberService->findOrCreateWithPart($message->member);

        $memberTenure = $message->member->joined_at->diffInDays(Carbon::now());

        $hasMetMembershipLength = $memberTenure >= self::MEMBER_TENURE_MINIMUM;
        $hasEnoughComments = $member->getTotalComments() >= self::MEMBER_COMMENTS_MINIMUM;
        $isAlreadyVerified = $message->member->roles->isset($verifiedRole);
        $hasSeriousDiscussion = $message->member->roles->isset('1114365923625816159');
        if ($message->guild->roles->isset('1460802399710089299')) {
            $svd = '1460802399710089299';
            $hasSdv = $message->guild->roles->isset($svd);
            if (!$hasSdv && $hasSeriousDiscussion && $isAlreadyVerified) {
                $message->member->addRole($svd);
            } elseif ($hasSdv) {
                $message->member->removeRole($svd);
            }
        }

        $isAlreadyUpgraded = $message->member->roles->isset($tenuredRoleId);
        if ($isAlreadyUpgraded) {
            return;
        }

        $canModerateMembers = $message->member->getPermissions()->moderate_members;

        $meetsRequirements = $hasMetMembershipLength && $hasEnoughComments;

        if (!$meetsRequirements && !$canModerateMembers && !$isAlreadyVerified) {
            return;
        }

        $message->member->addRole($tenuredRoleId);

        $message->guild->roles->fetch($tenuredRoleId)
            ->then(function (Role $role) use ($member, $output) {
                $this->spud->interact()
                    ->setTitle("Member Given {$role->name}")
                    ->setDescription(
                        "<@{$member->getDiscordId()}> met requirements to be given this role."
                    )->sendTo($output);
            });
    }

    public function canRun(?Message $message = null): bool
    {
        if (!$message) {
            return false;
        }
        $isRegularMember = $message->member && !$message->member->user->bot;
        $correctGuild = $message->guild_id === self::APPLIES_TO_GUILD;
        $hasJoinDate = $message->member?->joined_at instanceof Carbon;
        if (!$isRegularMember || !$correctGuild || !$hasJoinDate) {
            return false;
        }
        return true;
    }
}
