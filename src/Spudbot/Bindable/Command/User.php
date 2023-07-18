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

class User extends IBindableCommand
{
    protected string $name = 'user';
    protected string $description = 'Gets user associated user data.';

    public function getListener(): callable
    {
        if (empty($this->dbal)) {
            throw new \RuntimeException(
                "Command '{$this->getName()}' requires a DBAL Client to function appropriately."
            );
        }

        return function (Interaction $interaction) {
            $this->observer->subscribe(new TotalUserComments());
            $this->observer->subscribe(new UserEventReputation());
            $this->observer->subscribe(new UserInformation());
            $this->observer->subscribe(new UserLeaderboard());
            $this->observer->subscribe(new UserNoShowStatus());
//            $this->observer->setDefaultListener(function (Interaction $interaction){
//                $builder = $this->spud->getSimpleResponseBuilder();
//                $builder->setTitle('Test');
//                $builder->setDescription('Worked');
//                $interaction->respondWithMessage($builder->getEmbeddedMessage());
//            });

            $this->observer->notify($interaction->data->options, $interaction);
        };
    }

    public function getCommand(): Command
    {
        $totalComments = new Option($this->discord);
        $totalComments->setName('total_comments');
        $totalComments->setDescription('Get the users total comments.');
        $totalComments->setType(Option::SUB_COMMAND);

        $repSubCommand = new Option($this->discord);
        $repSubCommand->setName('reputation');
        $repSubCommand->setDescription("Get the users event reputation (percentage of attendance).");
        $repSubCommand->setType(Option::SUB_COMMAND);

        $noShowSubCommand = new Option($this->discord);
        $noShowSubCommand->setName('no_show');
        $noShowSubCommand->setDescription("Change a users no show status for the specified event.");
        $noShowSubCommand->setType(Option::SUB_COMMAND);

        $noShowStatus = new Option($this->discord);
        $noShowStatus->setName('status');
        $noShowStatus->setDescription("Should the user be considered a no-show?");
        $noShowStatus->setType(Option::BOOLEAN);
        $noShowStatus->setRequired(true);

        $eventId = new Option($this->discord);
        $eventId->setName('internal_id')
            ->setDescription('Internal Record ID for the event that needs to be updated.')
            ->setRequired(true)
            ->setType(Option::STRING);

        $leaderboardSubCommand = new Option($this->discord);
        $leaderboardSubCommand->setName('leaderboard');
        $leaderboardSubCommand->setDescription("Get the top 10 users with the most comments since tracking began.");
        $leaderboardSubCommand->setType(Option::SUB_COMMAND);
        $leaderboardSubCommand->addOption(
            (new Option($this->discord))
                ->setName('limit')
                ->setType(Option::INTEGER)
                ->setMinValue(5)
                ->setMaxValue(50)
                ->setDescription('How many members to show on the leaderboard.')
        );

        $userInfoSubCommand = new Option($this->discord);
        $userInfoSubCommand->setName('info');
        $userInfoSubCommand->setDescription("Information about the mentioned user.");
        $userInfoSubCommand->setType(Option::SUB_COMMAND);

        $reason = new Option($this->discord);
        $reason->setName('reason')
            ->setDescription('The reason this user should be verified.')
            ->setRequired(true)
            ->setType(Option::STRING);

        $user = new Option($this->discord);
        $user->setName('user')
            ->setDescription('The user that should be targeted.')
            ->setRequired(true)
            ->setType(Option::USER);

        $totalComments->addOption($user);
        $repSubCommand->addOption($user);
        $userInfoSubCommand->addOption($user);
        $noShowSubCommand->addOption($user);
        $noShowSubCommand->addOption($eventId);
        $noShowSubCommand->addOption($noShowStatus);

        $command = CommandBuilder::new();
        $command->setName($this->getName());
        $command->setDescription($this->getDescription());
        $command->addOption($totalComments);
        $command->addOption($repSubCommand);
        $command->addOption($leaderboardSubCommand);
        $command->addOption($userInfoSubCommand);
        $command->addOption($noShowSubCommand);

        return new Command($this->discord, $command->toArray());
    }
}