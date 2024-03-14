<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023-2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Commands;

use Carbon\Carbon;
use DI\Attribute\Inject;
use Discord\Builders\CommandBuilder;
use Discord\Parts\Channel\Channel;
use Discord\Parts\Interactions\Command\Command;
use Discord\Parts\Interactions\Command\Option;
use Discord\Parts\Interactions\Interaction;
use InvalidArgumentException;
use Spudbot\Interface\AbstractCommandSubscriber;
use Spudbot\Model\Reminder;
use Spudbot\Services\ChannelService;
use Spudbot\Services\ReminderService;
use Spudbot\Util\Recurrence;

class AddReminder extends AbstractCommandSubscriber
{
    #[Inject]
    protected ChannelService $channelService;
    #[Inject]
    protected ReminderService $reminderService;

    public function update(?Interaction $interaction = null): void
    {
        if (!$interaction) {
            return;
        }
        $builder = $this->spud->interact()
            ->setTitle('Scheduled Reminder');

        $reminderDescription = $interaction->data->options['message']->value;
        $scheduledAt = $interaction->data->options['datetime']->value;
        $repeats = $interaction->data->options['repeats'] ? $interaction->data->options['repeats']->value : null;

        $threadTypes = [
            Channel::TYPE_PUBLIC_THREAD,
            Channel::TYPE_PRIVATE_THREAD,
            Channel::TYPE_ANNOUNCEMENT_THREAD,
        ];

        if (in_array($interaction->channel->type, $threadTypes, true)) {
            $builder->setDescription('I cannot create reminders in threads at this time.');
            $builder->respondTo($interaction, true);
            return;
        }

        if ($repeats) {
            try {
                $repeats = Recurrence::getIntervalFromString($repeats);
            } catch (InvalidArgumentException $exception) {
                $builder->setDescription($exception->getMessage());
                $builder->respondTo($interaction, true);
                return;
            }

            if (!Recurrence::isIntervalLongEnough($repeats)) {
                $builder->setDescription(
                    'The specified interval is using units that are too small for a reminder.'
                );
                $builder->respondTo($interaction, true);
                return;
            }
        }

        $channel = $this->channelService->findOrCreateWithPart($interaction->channel);

        $guildTimeZone = $channel->getGuild()->getTimeZone();
        $scheduledAt = Carbon::parse($scheduledAt, $guildTimeZone);

        $reminder = new Reminder();
        $reminder->setDescription($reminderDescription);
        $reminder->setScheduledAt($scheduledAt);
        $reminder->setGuild($channel->getGuild());
        $reminder->setRepeats($repeats);
        $reminder->setChannel($channel);

        $this->reminderService->save($reminder);

        $message = "The reminder will be sent out at {$reminder->getLocalScheduledAt()->toDayDateTimeString()}";
        if (!empty($reminder->getRepeats())) {
            $message .= ", repeating every {$reminder->getRepeats()}";
        }

        $builder->setDescription($message)
            ->respondTo($interaction);
    }

    public function getCommand(): Command
    {
        $user = new Option($this->spud->discord);
        $user->setName('message')
            ->setDescription('The message of the reminder.')
            ->setRequired(true)
            ->setType(Option::STRING);

        $datetime = new Option($this->spud->discord);
        $datetime->setName('datetime')
            ->setDescription('When the reminder should be sent (US eastern timezone).')
            ->setRequired(true)
            ->setType(Option::STRING);

        $repeats = new Option($this->spud->discord);
        $repeats->setName('repeats')
            ->setDescription('How often should the reminder be repeated?')
            ->setRequired()
            ->setType(Option::STRING);

        $command = CommandBuilder::new();
        $command->setName($this->getCommandName())
            ->setDescription($this->getCommandDescription())
            ->addOption($user)
            ->addOption($datetime)
            ->addOption($repeats);

        return new Command($this->spud->discord, $command->toArray());
    }

    public function getCommandName(): string
    {
        return 'remind';
    }

    public function getCommandDescription(): string
    {
        return 'Creates a reminder at the specified datetime.';
    }
}
