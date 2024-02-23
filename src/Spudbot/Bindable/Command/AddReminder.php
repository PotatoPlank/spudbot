<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023-2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Bindable\Command;

use Carbon\Carbon;
use Discord\Builders\CommandBuilder;
use Discord\Parts\Interactions\Command\Command;
use Discord\Parts\Interactions\Command\Option;
use Discord\Parts\Interactions\Interaction;
use Spudbot\Interface\IBindableCommand;
use Spudbot\Model\Channel;
use Spudbot\Model\Reminder;
use Spudbot\Util\Recurrence;

class AddReminder extends IBindableCommand
{
    protected string $name = 'remind';
    protected string $description = 'Creates a reminder at the specified datetime.';

    public function getListener(): callable
    {
        return function (Interaction $interaction) {
            $builder = $this->spud->getSimpleResponseBuilder();
            $builder->setTitle('Scheduled Reminder');
            $reminderDescription = $interaction->data->options['message']->value;
            $scheduledAt = $interaction->data->options['datetime']->value;
            $repeats = $interaction->data->options['repeats'] ? $interaction->data->options['repeats']->value : null;

            $threadTypes = [
                \Discord\Parts\Channel\Channel::TYPE_PUBLIC_THREAD,
                \Discord\Parts\Channel\Channel::TYPE_PRIVATE_THREAD,
                \Discord\Parts\Channel\Channel::TYPE_ANNOUNCEMENT_THREAD,
            ];

            if (in_array($interaction->channel->type, $threadTypes, true)) {
                $builder->setDescription('I cannot create reminders in threads at this time.');
                $interaction->respondWithMessage($builder->getEmbeddedMessage(), true);
                return;
            }

            if ($repeats) {
                try {
                    $repeats = Recurrence::getIntervalFromString($repeats);
                } catch (\InvalidArgumentException $exception) {
                    $builder->setDescription($exception->getMessage());
                    $interaction->respondWithMessage($builder->getEmbeddedMessage(), true);
                    return;
                }

                if (!Recurrence::isIntervalLongEnough($repeats)) {
                    $builder->setDescription(
                        'The specified interval is using units that are too small for a reminder.'
                    );
                    $interaction->respondWithMessage($builder->getEmbeddedMessage(), true);
                    return;
                }
            }

            try {
                $channel = $this->spud->channelRepository->findByPart($interaction->channel);
            } catch (\OutOfBoundsException $exception) {
                $channel = new Channel();
                $channel->setGuild($this->spud->guildRepository->findByPart($interaction->guild));
                $channel->setDiscordId($interaction->channel->id);
                $this->spud->channelRepository->save($channel);
            }

            $guildTimeZone = $channel->getGuild()->getTimeZone();
            $scheduledAt = Carbon::parse($scheduledAt, $guildTimeZone);
            $reminder = new Reminder();
            $reminder->setDescription($reminderDescription);
            $reminder->setScheduledAt($scheduledAt);
            $reminder->setGuild($channel->getGuild());
            $reminder->setRepeats($repeats);
            $reminder->setChannel($channel);

            $this->spud->reminderRepository->save($reminder);

            $message = "The reminder will be sent out at {$reminder->getLocalScheduledAt()->toDayDateTimeString()}";
            if (!empty($reminder->getRepeats())) {
                $message .= ", repeating every {$reminder->getRepeats()}";
            }

            $builder->setDescription(
                $message
            );

            $interaction->respondWithMessage($builder->getEmbeddedMessage());
        };
    }

    public function getCommand(): Command
    {
        $user = new Option($this->discord);
        $user->setName('message')
            ->setDescription('The message of the reminder.')
            ->setRequired(true)
            ->setType(Option::STRING);

        $datetime = new Option($this->discord);
        $datetime->setName('datetime')
            ->setDescription('When the reminder should be sent (US eastern timezone).')
            ->setRequired(true)
            ->setType(Option::STRING);

        $repeats = new Option($this->discord);
        $repeats->setName('repeats')
            ->setDescription('How often should the reminder be repeated?')
            ->setRequired()
            ->setType(Option::STRING);

        $command = CommandBuilder::new();
        $command->setName($this->getName())
            ->setDescription($this->getDescription())
            ->addOption($user)
            ->addOption($datetime)
            ->addOption($repeats);

        return new Command($this->discord, $command->toArray());
    }

    public function checkRequirements(): void
    {
        if (empty($this->spud->dbal)) {
            throw new \RuntimeException(
                "Command '{$this->getName()}' requires a DBAL Client to function appropriately."
            );
        }
    }
}
