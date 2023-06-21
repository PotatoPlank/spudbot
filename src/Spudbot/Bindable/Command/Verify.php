<?php

namespace Spudbot\Bindable\Command;

use Discord\Builders\CommandBuilder;
use Discord\Parts\Interactions\Command\Command;
use Discord\Parts\Interactions\Command\Option;
use Discord\Parts\Interactions\Interaction;
use Spudbot\Bindable\SubCommand\TotalUserComments;
use Spudbot\Bindable\SubCommand\UserEventReputation;
use Spudbot\Bindable\SubCommand\UserInformation;
use Spudbot\Bindable\SubCommand\UserLeaderboard;
use Spudbot\Bindable\SubCommand\UserNoShowStatus;
use Spudbot\Interface\IBindableCommand;

class Verify extends IBindableCommand
{
    protected string $name = 'verify';
    protected string $description = 'Verifies a user, vouching for their authenticity.';

    public function getListener(): callable
    {
        if (empty($this->dbal)) {
            throw new \RuntimeException(
                "Command '{$this->getName()}' requires a DBAL Client to function appropriately."
            );
        }

        return function (Interaction $interaction) {
            $builder = $this->spud->getSimpleResponseBuilder();
            $builder->setTitle('User Verification');
            $targetMemberId = $interaction->data->options['user']->value;
            $verificationReason = $interaction->data->options['reason']->value;

            $memberToBeVerified = $interaction->guild->members->get('id', $targetMemberId);
            $sourceMemberIsVerified = $interaction->member->roles->isset(1114365923730665482);

            if ($memberToBeVerified) {
                $builder->setDescription('An invalid user was submitted for verification.');
                $interaction->respondWithMessage($builder->getEmbeddedMessage(), true);
                return;
            }

            if($interaction->member->id === $memberToBeVerified->id){
                $builder->setDescription('You cannot verify yourself.');
                $interaction->respondWithMessage($builder->getEmbeddedMessage(), true);
                return;
            }

            $guild = $this->spud->getGuildRepository()->findByPart($interaction->guild);
            $output = $interaction->guild->channels->get('id', $guild->getOutputChannelId());
            if($guild->isOutputLocationThread()){
                $output = $output->threads->get('id', $guild->getOutputThreadId());
            }

            $context = [
                'sourceMemberId' => $interaction->member->id,
                'targetMemberId' => $memberToBeVerified->id,
                'reason' => $verificationReason,
            ];

            if ($sourceMemberIsVerified) {
                $builder->setDescription($this->spud->getTwig()->render('verification.twig', $context));

                $interaction->respondWithMessage($builder->getEmbeddedMessage());
                $output->sendMessage($builder->getEmbeddedMessage());
                return;
            }

            $builder->setDescription('You do not have the required permissions to verify.');
            $interaction->respondWithMessage($builder->getEmbeddedMessage(), true);

            $builder->setDescription($this->spud->getTwig()->render('verification_error.twig', $context));
            $output->sendMessage($builder->getEmbeddedMessage());
        };
    }

    public function getCommand(): Command
    {
        $user = new Option($this->discord);
        $user->setName('user')
            ->setDescription('The user that should be targeted.')
            ->setRequired(true)
            ->setType(Option::USER);

        $reason = new Option($this->discord);
        $reason->setName('reason')
            ->setDescription('The reason this user should be verified.')
            ->setRequired(true)
            ->setType(Option::STRING);

        $command = CommandBuilder::new();
        $command->setName($this->getName())
            ->setDescription($this->getDescription())
            ->addOption($user)->addOption($reason);

        return new Command($this->discord, $command->toArray());
    }
}