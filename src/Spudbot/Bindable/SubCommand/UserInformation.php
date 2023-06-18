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
        $hasMetMembershipLength = $memberLength >= 10;
        $hasEnoughComments = $member->getTotalComments() >= 10;
        $isEligible = $hasMetMembershipLength && $hasEnoughComments;

        $message = "<@{$member->getDiscordId()}> has been a member for {$memberLength->days}/10 days." . PHP_EOL;
        $message .= "They have posted {$member->getTotalComments()}/10 comments." . PHP_EOL;
        $message .= $isLevelOne ? "They are {$levelOneRole->name}." . PHP_EOL : "They are not {$levelOneRole->name}." . PHP_EOL;
        $message .= $isVerified ? "They are {$verificationRole->name}." . PHP_EOL : "They are not {$verificationRole->name}." . PHP_EOL;
        $message .= $isEligible ? "They are eligible for {$levelOneRole->name}." . PHP_EOL : "They are not eligible for {$levelOneRole->name}." . PHP_EOL;

        $builder->setTitle($title);
        $builder->setDescription($message);

        $interaction->respondWithMessage($builder->getEmbeddedMessage());
    }
}