<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Commands;

use Discord\Builders\CommandBuilder;
use Discord\Parts\Interactions\Command\Command;
use Discord\Parts\Interactions\Command\Option;
use Discord\Parts\Interactions\Interaction;
use Spudbot\Interface\AbstractCommandSubscriber;
use Spudbot\SubCommands\TotalUserComments;
use Spudbot\SubCommands\UserEventReputation;
use Spudbot\SubCommands\UserInformation;
use Spudbot\SubCommands\UserLeaderboard;
use Spudbot\SubCommands\UserNoShowStatus;

class User extends AbstractCommandSubscriber
{

    public function update(?Interaction $interaction = null): void
    {
        if (!$interaction) {
            return;
        }
        $this->subCommandObserver->subscribeAll([
            TotalUserComments::class,
            UserEventReputation::class,
            UserInformation::class,
            UserLeaderboard::class,
            UserNoShowStatus::class,
        ]);
//        $this->subCommandObserver->setDefaultListener(function (Interaction $interaction) {
//            $builder = $this->spud->getSimpleResponseBuilder();
//            $builder->setTitle('Test');
//            $builder->setDescription('Worked');
//            $interaction->respondWithMessage($builder->getEmbeddedMessage());
//        });

        $this->subCommandObserver->notify($interaction->data->options, $interaction);
    }

    public function getCommand(): Command
    {
        $totalComments = new Option($this->spud->discord);
        $totalComments->setName('total_comments');
        $totalComments->setDescription('Get the users total comments.');
        $totalComments->setType(Option::SUB_COMMAND);

        $repSubCommand = new Option($this->spud->discord);
        $repSubCommand->setName('reputation');
        $repSubCommand->setDescription("Get the users event reputation (percentage of attendance).");
        $repSubCommand->setType(Option::SUB_COMMAND);

        $noShowSubCommand = new Option($this->spud->discord);
        $noShowSubCommand->setName('no_show');
        $noShowSubCommand->setDescription("Change a users no show status for the specified event.");
        $noShowSubCommand->setType(Option::SUB_COMMAND);

        $noShowStatus = new Option($this->spud->discord);
        $noShowStatus->setName('status');
        $noShowStatus->setDescription("Should the user be considered a no-show?");
        $noShowStatus->setType(Option::BOOLEAN);
        $noShowStatus->setRequired(true);

        $eventId = new Option($this->spud->discord);
        $eventId->setName('internal_id')
            ->setDescription('Internal Record ID for the event that needs to be updated.')
            ->setRequired(true)
            ->setType(Option::STRING);

        $leaderboardSubCommand = new Option($this->spud->discord);
        $leaderboardSubCommand->setName('leaderboard');
        $leaderboardSubCommand->setDescription("Get the top 10 users with the most comments since tracking began.");
        $leaderboardSubCommand->setType(Option::SUB_COMMAND);
        $leaderboardSubCommand->addOption(
            (new Option($this->spud->discord))
                ->setName('limit')
                ->setType(Option::INTEGER)
                ->setMinValue(5)
                ->setMaxValue(50)
                ->setDescription('How many members to show on the leaderboard.')
        );

        $userInfoSubCommand = new Option($this->spud->discord);
        $userInfoSubCommand->setName('info');
        $userInfoSubCommand->setDescription("Information about the mentioned user.");
        $userInfoSubCommand->setType(Option::SUB_COMMAND);

        $reason = new Option($this->spud->discord);
        $reason->setName('reason')
            ->setDescription('The reason this user should be verified.')
            ->setRequired(true)
            ->setType(Option::STRING);

        $user = new Option($this->spud->discord);
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
        $command->setName($this->getCommandName());
        $command->setDescription($this->getCommandDescription());
        $command->addOption($totalComments);
        $command->addOption($repSubCommand);
        $command->addOption($leaderboardSubCommand);
        $command->addOption($userInfoSubCommand);
        $command->addOption($noShowSubCommand);

        return new Command($this->spud->discord, $command->toArray());
    }

    public function getCommandName(): string
    {
        return 'user_test';
    }

    public function getCommandDescription(): string
    {
        return 'Gets user associated user data.';
    }
}
