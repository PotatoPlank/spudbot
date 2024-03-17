<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023-2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\SubCommands;


use Carbon\Carbon;
use DI\Attribute\Inject;
use Discord\Parts\Interactions\Interaction;
use Spudbot\Services\MemberService;

class UserInformation extends AbstractSubCommandSubscriber
{
    #[Inject]
    protected MemberService $memberService;

    public function update(?Interaction $interaction = null): void
    {
        if (!$interaction) {
            return;
        }
        $title = 'User Information';
        $userId = $this->options['user']->value;
        $memberPart = $interaction->guild->members->get('id', $userId);
        if (!$memberPart) {
            $this->spud->interact()
                ->error("Unable to find member $userId");
            return;
        }
        $levelOneRole = $interaction->guild->roles->get('id', 1114365923730665481);
        $verificationRole = $interaction->guild->roles->get('id', 1114365923730665482);
        if (!$levelOneRole || !$verificationRole) {
            $this->spud->interact()
                ->error("Unable to find role levels.")
                ->respondTo($interaction);
            return;
        }

        $member = $this->memberService->findOrCreateWithPart($memberPart);

        $memberName = $member->getUsername();
        $memberLength = $memberPart->joined_at->diff(Carbon::now());
        $isLevelOne = $memberPart->roles->isset($levelOneRole->id);
        $isVerified = $memberPart->roles->isset($verificationRole->id);
        $hasMetMembershipLength = $memberLength >= $_ENV['MEMBER_TENURE'];
        $hasEnoughComments = $member->hasMetCommentThreshold();
        $isEligible = $hasMetMembershipLength && $hasEnoughComments;

        if ($member->getVerifiedBy()) {
            $verifierName = $member->getVerifiedBy()->getUsername();
            $verifierId = $member->getVerifiedBy()->getDiscordId();
        }

        $botStatus = $memberPart->user->bot ? 'Yes' : 'No';
        if ($memberPart->user->bot === null) {
            $botStatus = 'Not flagged';
        }

        $this->spud->interact()
            ->setTitle($title)
            ->setDescription(
                $this->spud->twig->render('user/information.twig', [
                    'memberId' => $member->getDiscordId(),
                    'memberName' => $memberName,
                    'tenureDays' => $memberLength->days,
                    'requiredTenureDays' => $_ENV['MEMBER_TENURE'],
                    'totalComments' => $member->getTotalComments(),
                    'requiredCommentCount' => $_ENV['MEMBER_COMMENT_THRESHOLD'],
                    'isLevelOne' => $isLevelOne,
                    'isVerified' => $isVerified,
                    'isEligible' => $isEligible,
                    'levelOneRoleName' => $levelOneRole->name,
                    'verifiedRoleName' => $verificationRole->name,
                    'verifiedById' => $verifierId ?? null,
                    'verifiedByName' => $verifierName ?? null,
                    'isBot' => $botStatus,
                ])
            )
            ->respondTo($interaction);
    }

    public function getCommandName(): string
    {
        return 'info';
    }
}
