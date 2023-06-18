<?php

namespace Spudbot\Bindable\SubCommand;


use Carbon\Carbon;
use Discord\Parts\Interactions\Interaction;
use Spudbot\Interface\ISubCommand;
use Spudbot\Repository\SQL\MemberRepository;

class UserInformation extends ISubCommand
{
    protected string $subCommand = 'info';
    public function execute(?Interaction $interaction): void
    {
        /**
         * @var MemberRepository $memberRepository
         */
        $memberRepository = $this->spud->getMemberRepository();
        $title = 'User Information';
        $builder = $this->spud->getSimpleResponseBuilder();
        $userId = $this->options['user']->value;
        $memberPart = $interaction->guild->members->get('id', $userId);
        $member = $memberRepository->findByPart($memberPart);

        $memberLength = $memberPart->joined_at->diff(Carbon::now());
        $levelOneRole = $interaction->guild->roles->get('id', 1114365923730665481);
        $verificationRole = $interaction->guild->roles->get('id', 1114365923730665482);
        $isLevelOne = $memberPart->roles->isset($levelOneRole->id);
        $isVerified = $memberPart->roles->isset($verificationRole->id);
        $hasMetMembershipLength = $memberLength >= $_ENV['MEMBER_TENURE'];
        $hasEnoughComments = $member->getTotalComments() >= $_ENV['MEMBER_COMMENT_THRESHOLD'];
        $isEligible = $hasMetMembershipLength && $hasEnoughComments;

        $context = [
            'memberId' => $member->getDiscordId(),
            'tenureDays' => $memberLength->days,
            'requiredTenureDays' => $_ENV['MEMBER_TENURE'],
            'totalComments' => $member->getTotalComments(),
            'requiredCommentCount' => $_ENV['MEMBER_COMMENT_THRESHOLD'],
            'isLevelOne' => $isLevelOne,
            'isVerified' => $isVerified,
            'isEligible' => $isEligible,
            'levelOneRoleName' => $levelOneRole->name,
            'verifiedRoleName' => $verificationRole->name,
        ];

        $builder->setTitle($title);
        $builder->setDescription($this->spud->getTwig()->render('user/information.twig', $context));

        $interaction->respondWithMessage($builder->getEmbeddedMessage());
    }
}