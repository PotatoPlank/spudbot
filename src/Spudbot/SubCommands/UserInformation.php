<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023-2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\SubCommands;


use Carbon\Carbon;
use Discord\Parts\Interactions\Interaction;
use Spudbot\Interface\AbstractSubCommandSubscriber;
use Spudbot\Repository\SQL\MemberRepository;

class UserInformation extends AbstractSubCommandSubscriber
{

    public function update(?Interaction $interaction = null): void
    {
        /**
         * @var MemberRepository $memberRepository
         */
        $memberRepository = $this->spud->memberRepository;
        $title = 'User Information';
        $builder = $this->spud->getSimpleResponseBuilder();
        $userId = $this->options['user']->value;
        $memberPart = $interaction->guild->members->get('id', $userId);
        $member = $memberRepository->findByPart($memberPart);
        $memberName = $member->getUsername();

        $memberLength = $memberPart->joined_at->diff(Carbon::now());
        $levelOneRole = $interaction->guild->roles->get('id', 1114365923730665481);
        $verificationRole = $interaction->guild->roles->get('id', 1114365923730665482);
        $isLevelOne = $memberPart->roles->isset($levelOneRole->id);
        $isVerified = $memberPart->roles->isset($verificationRole->id);
        $hasMetMembershipLength = $memberLength >= $_ENV['MEMBER_TENURE'];
        $hasEnoughComments = $member->hasMetCommentThreshold();
        $isEligible = $hasMetMembershipLength && $hasEnoughComments;

        if ($member->getVerifiedBy()) {
            $verifier = $memberRepository->findById($member->getVerifiedBy());
            $verifierName = $verifier->getUsername();
            $verifierId = $verifier->getDiscordId();
        }

        $context = [
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
        ];

        $builder->setTitle($title);
        $builder->setDescription($this->spud->twig->render('user/information.twig', $context));

        $interaction->respondWithMessage($builder->getEmbeddedMessage());
    }

    public function getCommandName(): string
    {
        return 'info';
    }
}
