<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023-2024. PotatoPlank <potatoplank@protonmail.com>
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
use Spudbot\Services\GuildService;
use Spudbot\Services\MemberService;

class ApplyMemberRoleUpgrades extends AbstractEventSubscriber
{
    private const APPLIES_TO_GUILD = '1114365923625816155';
    private const LEVEL_ONE_ROLE = '1114365923730665481';
    private const VERIFIED_ROLE = '1114365923730665482';
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

        $this->spud->discord->getLogger()
            ->info("Checking to upgrade the membership of {$message->member->displayname}");

        $guild = $this->guildService->findOrCreateWithPart($message->member->guild);

        try {
            $output = $guild->getOutputPart($message->guild);
        } catch (Exception $exception) {
            $this->spud->discord->getLogger()
                ->error($exception->getMessage());
            return;
        }

        $member = $this->memberService->findOrCreateWithPart($message->member);

        $memberTenure = $message->member->joined_at->diffInDays(Carbon::now());

        $hasMetMembershipLength = $memberTenure >= self::MEMBER_TENURE_MINIMUM;
        $hasEnoughComments = $member->getTotalComments() >= self::MEMBER_COMMENTS_MINIMUM;

        $isAlreadyUpgraded = $message->member->roles->isset(self::LEVEL_ONE_ROLE);
        if ($isAlreadyUpgraded) {
            return;
        }
        $isAlreadyVerified = $message->member->roles->isset(self::VERIFIED_ROLE);
        $canModerateMembers = $message->member->getPermissions()->moderate_members;

        $meetsRequirements = $hasMetMembershipLength && $hasEnoughComments;

        if (!$meetsRequirements && !$canModerateMembers && !$isAlreadyVerified) {
            return;
        }

        $message->member->addRole(self::LEVEL_ONE_ROLE);

        $message->guild->roles->fetch(self::LEVEL_ONE_ROLE)
            ->done(function (Role $role) use ($member, $output) {
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
