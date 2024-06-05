<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023-2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Commands;

use DI\Attribute\Inject;
use Discord\Builders\CommandBuilder;
use Discord\Parts\Interactions\Command\Command;
use Discord\Parts\Interactions\Command\Option;
use Discord\Parts\Interactions\Interaction;
use Discord\Parts\User\Member;
use OutOfBoundsException;
use Spudbot\Model\Guild;
use Spudbot\Services\GuildService;
use Spudbot\Services\MemberService;

class Verify extends AbstractCommandSubscriber
{
    protected const INVALID_USER = 'An invalid user was submitted for verification.';
    protected const SELF_VERIFY = 'You cannot verify yourself.';
    protected const PERMISSIONS = 'You do not have the required permissions to verify.';
    #[Inject]
    protected GuildService $guildService;
    #[Inject]
    protected MemberService $memberService;
    protected array $storage = [];

    public function update(?Interaction $interaction = null): void
    {
        if (!$interaction) {
            return;
        }
        $guild = $this->guildService->findOrCreateWithPart($interaction->guild);
        if (!$guild->hasVerifiedRole()) {
            return;
        }
        $verifiedId = $guild->verifiedMembersRoleId;
        $this->storage[$guild->discordId] = $verifiedId;
        $botLogChannel = $guild->getChannelThreadPart(Guild::BOT_LOG_CHANNEL, $interaction->guild);
        $verifiedChannel = $guild->getChannelThreadPart(Guild::VERIFIED_CHANNEL, $interaction->guild);

        $builder = $this->spud->interact()
            ->setTitle('User Verification');
        $targetMemberId = $interaction->data->options['user']->value;
        $verificationReason = $interaction->data->options['reason']->value;
        $sourceMemberName = $interaction->member->nick ?? $interaction->member->displayname;

        $memberToBeVerified = $interaction->guild->members->get('id', $targetMemberId);

        $errorMessage = $this->getError($interaction, $memberToBeVerified);

        $context = [
            'sourceMemberId' => $interaction->member->id,
            'targetMemberId' => $memberToBeVerified?->id,
            'reason' => $verificationReason,
        ];

        if ($errorMessage !== null) {
            $this->spud->interact()
                ->error($errorMessage)
                ->respondTo($interaction, true);

            if ($errorMessage === self::PERMISSIONS) {
                $builder->setDescription($this->spud->twig->render('user/verification_error.twig', $context));
                $builder->sendTo($botLogChannel);
            }
            return;
        }

        $memberToBeVerified->addRole($verifiedId, "Verified by {$sourceMemberName}");

        $builder->setDescription($this->spud->twig->render('user/verification.twig', $context));


        $verifyingMember = $this->memberService->findOrCreateWithPart($interaction->member);
        try {
            $verifiedMember = $this->memberService->findOrCreateWithPart($memberToBeVerified);
            $verifiedMember->setVerifiedBy($verifyingMember);

            $this->memberService->save($verifiedMember);
        } catch (OutOfBoundsException $exception) {
            $builder->setDescription(
                "Unable to verify <@{$memberToBeVerified->id}>, they haven't made any comments."
            );
            error_log($exception);
        }

        $builder->respondTo($interaction, true);
        $builder->sendTo($botLogChannel);
        $builder->sendTo($verifiedChannel);
    }

    protected function getError(Interaction $interaction, ?Member $targetMember): ?string
    {
        $message = null;
        if (!$targetMember) {
            $message = self::INVALID_USER;
        }
        if ($interaction->member->id === $targetMember->id) {
            $message = self::SELF_VERIFY;
        }
        if (!$interaction->member->roles->isset($this->storage[$interaction->guild_id])) {
            $message = self::PERMISSIONS;
        }
        return $message;
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
