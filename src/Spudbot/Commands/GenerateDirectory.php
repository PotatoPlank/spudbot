<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023-2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Commands;

use DI\Attribute\Inject;
use Discord\Builders\CommandBuilder;
use Discord\Parts\Channel\Message;
use Discord\Parts\Interactions\Command\Command;
use Discord\Parts\Interactions\Command\Option;
use Discord\Parts\Interactions\Interaction;
use Spudbot\Interface\AbstractCommandSubscriber;
use Spudbot\Model\Directory;
use Spudbot\Parsers\DirectoryParser;
use Spudbot\Services\ChannelService;
use Spudbot\Services\DirectoryService;

class GenerateDirectory extends AbstractCommandSubscriber
{
    #[Inject]
    protected ChannelService $channelService;
    #[Inject]
    protected DirectoryService $directoryService;
    #[Inject]
    protected DirectoryParser $directoryParser;

    public function update(?Interaction $interaction = null): void
    {
        if (!$interaction) {
            return;
        }

        $response = $this->spud->interact()
            ->setTitle('Forum Directory');


        $formChannelId = $interaction->data->options['channel']->value;
        $forumChannelPart = $interaction->guild->channels->get('id', $formChannelId);
        $directoryChannelPart = $interaction->channel;

        if (!$forumChannelPart) {
            $response->error('Unable to locate the specified forum channel')
                ->respondTo($interaction);
            return;
        }

        $forumChannel = $this->channelService->findOrCreateWithPart($forumChannelPart);
        $directoryChannel = $this->channelService->findOrCreateWithPart($directoryChannelPart);

        try {
            $directory = $this->directoryService->findWithForumChannel($forumChannel);
            if (!$directory) {
                throw new \OutOfBoundsException('Directory does not exist.');
            }
            $forumDirectoryPart = $interaction->guild
                ->channels->get('id', $directory->getDirectoryChannel()->getDiscordId());

            if (!$forumDirectoryPart) {
                $response->error('Unable to locate the specified forum directory')
                    ->respondTo($interaction);
                return;
            }
            $directoryMessage = $this->directoryParser->fromPart($forumChannelPart)
                ->getBody();

            $embed = $this->spud->interact()
                ->setTitle($directory->getTitle($forumChannelPart))
                ->setDescription($directoryMessage);

            $success = function (Message $message) use ($embed) {
                $message->edit($embed->build());
            };

            $rejected = function () use ($directoryChannelPart, $embed, $directory) {
                $embed->sendTo($directoryChannelPart)
                    ->done(function (Message $message) use ($directory) {
                        $directory->setEmbedId($message->id);

                        $this->directoryService
                            ->save($directory);
                    });
            };

            $forumDirectoryPart->messages->fetch($directory->getEmbedId())
                ->done($success, $rejected);

            $response->setDescription('Updated the directory.');
        } catch (\OutOfBoundsException $exception) {
            $this->spud->discord->getLogger()->info($exception->getMessage());

            $directory = new Directory();
            $directory->setDirectoryChannel($directoryChannel);
            $directory->setForumChannel($forumChannel);

            $directoryMessage = $this->directoryParser->fromPart($forumChannelPart)
                ->getBody();

            $this->spud->interact()
                ->setTitle($directory->getTitle($forumChannelPart))
                ->setDescription($directoryMessage)
                ->sendTo($directoryChannelPart)
                ->done(function (Message $message) use ($directory) {
                    $directory->setEmbedId($message->id);

                    $this->directoryService->save($directory);
                });

            $response->setDescription('Directory generated.');
        }
        $response->respondTo($interaction, true);
    }

    public function getCommand(): Command
    {
        $channel = new Option($this->spud->discord);
        $channel->setName('channel')
            ->setDescription('The forum channel.')
            ->setRequired(true)
            ->setType(Option::CHANNEL);

        $command = CommandBuilder::new();
        $command->setName($this->getCommandName())
            ->setDescription($this->getCommandDescription())
            ->addOption($channel);

        return new Command($this->spud->discord, $command->toArray());
    }

    public function getCommandName(): string
    {
        return 'forum_directory';
    }

    public function getCommandDescription(): string
    {
        return 'Creates a generated forum directory from the specified channel.';
    }
}
