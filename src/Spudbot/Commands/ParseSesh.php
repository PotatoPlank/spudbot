<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024-2025. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Commands;

use DI\Attribute\Inject;
use Discord\Builders\CommandBuilder;
use Discord\Parts\Interactions\Command\Command;
use Discord\Parts\Interactions\Command\Option;
use Discord\Parts\Interactions\Interaction;
use Discord\Parts\User\Member;
use Spudbot\Parsers\Sesh\SeshParser;
use Spudbot\Services\MemberService;

class ParseSesh extends AbstractCommandSubscriber
{
    #[Inject]
    protected MemberService $memberService;

    public function getCommand(): Command
    {
        $messageId = new Option($this->spud->discord);
        $messageId->setName('message_link')
            ->setDescription('The message that should be parsed.')
            ->setRequired(true)
            ->setType(Option::STRING);

        $command = CommandBuilder::new();
        $command->setName($this->getCommandName())
            ->setDescription($this->getCommandDescription())
            ->addOption($messageId);

        return new Command($this->spud->discord, $command->toArray());
    }

    public function getCommandName(): string
    {
        return 'parse_sesh';
    }

    public function getCommandDescription(): string
    {
        return 'Parse sesh event and dump information.';
    }

    public function update(?Interaction $interaction = null): void
    {
        if (!$interaction) {
            return;
        }

        if (!$this->isGuildManager($interaction->member)) {
            $this->spud->interact()
                ->error('You don\'t have the necessary permissions to run this command.')
                ->respondTo($interaction);
            return;
        }

        $this->parseSesh($interaction);
    }

    private function parseSesh(Interaction $interaction): void
    {
        // https://discord.com/channels/1114365923625816155/1114365926284992565/1424782459634978969
        // guild = 1114365923625816155
        // channel = 1114365926284992565
        // message = 1424782459634978969
        $messageLink = $interaction->data->options['message_link']->value;
        $parts = explode('/', str_replace('https://discord.com/channels/', '', $messageLink));
        if (count($parts) !== 3) {
            $this->spud->interact()
                ->setTitle('Invalid message link')
                ->setDescription('Invalid message link.')
                ->respondTo($interaction);
            return;
        }
        [$guildId, $channelId, $messageId] = $parts;
        if ($guildId !== $interaction->guild->id) {
            $this->spud->interact()
                ->setTitle('Wrong Guild')
                ->setDescription('Unable to parse links from other servers.')
                ->respondTo($interaction);
            return;
        }
        $channel = $this->spud->discord->getChannel($channelId);
        if (!$channel) {
            $this->spud->interact()
                ->setTitle('Failed finding channel')
                ->setDescription('Channel was unable to be fetched.')
                ->respondTo($interaction);
            return;
        }
        $channel->messages->fetch($messageId)
            ->then(function ($message) use ($interaction) {
                $messageContent = '';
                $seshEmbed = SeshParser::fromMessage($message);
                foreach ($seshEmbed->members as $eventStatus => $attendees) {
                    $statusContentText = '';
                    /**
                     * @var Member $attendee
                     */
                    foreach ($attendees as $attendee) {
                        $member = $this->memberService->findOrCreateWithPart($attendee);
                        $statusContentText .= "<@{$member->getDiscordId()}>" . PHP_EOL;
                    }
                    if (!empty($statusContentText)) {
                        $messageContent .= "{$eventStatus}:" . PHP_EOL . PHP_EOL . $statusContentText;
                    }
                }

                $messageContent = trim($messageContent);
                if (!empty($messageContent)) {
                    $this->spud->interact()
                        ->setTitle("{$seshEmbed->title} {$seshEmbed->seshTimeString}")
                        ->setDescription($messageContent)
                        ->setAllowedMentions([])
                        ->respondTo($interaction);
                }
            });
    }
}
