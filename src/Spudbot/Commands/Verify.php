<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023-2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Commands;

use Discord\Builders\CommandBuilder;
use Discord\Parts\Interactions\Command\Command;
use Discord\Parts\Interactions\Command\Option;
use Discord\Parts\Interactions\Interaction;
use Spudbot\Interface\AbstractCommandSubscriber;

class Verify extends AbstractCommandSubscriber
{
    public function update(?Interaction $interaction = null): void
    {
        if (!$interaction) {
            return;
        }

        $builder = $this->spud->interact()
            ->setTitle('User Verification');
        $targetMemberId = $interaction->data->options['user']->value;
        $verificationReason = $interaction->data->options['reason']->value;
        $sourceMemberName = $interaction->member->nick ?? $interaction->member->displayname;

        $memberToBeVerified = $interaction->guild->members->get('id', $targetMemberId);
        $sourceMemberIsVerified = $interaction->member->roles->isset(1114365923730665482);

        if (!$memberToBeVerified) {
            $this->spud->interact()
                ->error('An invalid user was submitted for verification.')
                ->respondTo($interaction, true);
            return;
        }

        if ($interaction->member->id === $memberToBeVerified->id) {
            $this->spud->interact()
                ->error('You cannot verify yourself.')
                ->respondTo($interaction, true);
            return;
        }

        $guild = $this->spud->guildRepository->findByPart($interaction->guild);
        $output = $guild->getOutputPart($interaction->guild);

        $context = [
            'sourceMemberId' => $interaction->member->id,
            'targetMemberId' => $memberToBeVerified->id,
            'reason' => $verificationReason,
        ];

        if (!$sourceMemberIsVerified) {
            $builder->error('You do not have the required permissions to verify.')
                ->respondTo($interaction, true);

            $builder->setDescription($this->spud->twig->render('user/verification_error.twig', $context));
            $builder->sendTo($output);
            return;
        }

        $memberToBeVerified->addRole(1114365923730665482, "Verified by {$sourceMemberName}");

        $builder->setDescription($this->spud->twig->render('user/verification.twig', $context));


        $verifyingMember = $this->spud->memberRepository->findByPart($interaction->member);
        try {
            $verifiedMember = $this->spud->memberRepository->findByPart($memberToBeVerified);
            $verifiedMember->setVerifiedBy($verifyingMember->getId());

            $this->spud->memberRepository->save($verifiedMember);
        } catch (\OutOfBoundsException $exception) {
            $builder->setDescription(
                "Unable to verify <@{$memberToBeVerified->id}>, they haven't made any comments."
            );
        }

        $builder->respondTo($interaction);
        $builder->sendTo($output);
    }

    public function getCommand(): Command
    {
        $user = new Option($this->spud->discord);
        $user->setName('user')
            ->setDescription('The user that should be targeted.')
            ->setRequired(true)
            ->setType(Option::USER);

        $reason = new Option($this->spud->discord);
        $reason->setName('reason')
            ->setDescription('The reason this user should be verified.')
            ->setRequired(true)
            ->setType(Option::STRING);

        $command = CommandBuilder::new();
        $command->setName($this->getCommandName())
            ->setDescription($this->getCommandDescription())
            ->addOption($user)->addOption($reason);

        return new Command($this->spud->discord, $command->toArray());
    }

    public function getCommandName(): string
    {
        return 'verify';
    }

    public function getCommandDescription(): string
    {
        return 'Verifies a user, vouching for their authenticity.';
    }
}
