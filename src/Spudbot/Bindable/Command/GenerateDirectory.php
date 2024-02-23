<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023-2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Bindable\Command;

use Discord\Builders\CommandBuilder;
use Discord\Parts\Channel\Message;
use Discord\Parts\Interactions\Command\Command;
use Discord\Parts\Interactions\Command\Option;
use Discord\Parts\Interactions\Interaction;
use Spudbot\Interface\IBindableCommand;
use Spudbot\Model\Channel;
use Spudbot\Model\Directory;

class GenerateDirectory extends IBindableCommand
{
    protected string $name = 'forum_directory';
    protected string $description = 'Creates a generated forum directory from the specified channel.';

    public function getListener(): callable
    {
        return function (Interaction $interaction) {
            $directoryRepository = $this->spud->directoryRepository;
            $response = $this->spud->getSimpleResponseBuilder();
            $response->setTitle('Forum Directory');


            $formChannelId = $interaction->data->options['channel']->value;
            $formChannelPart = $interaction->guild->channels->get('id', $formChannelId);
            $directoryChannelPart = $interaction->channel;


            if ($formChannelPart) {
                try {
                    $forumChannel = $this->spud->channelRepository
                        ->findByPart($formChannelPart);
                } catch (\OutOfBoundsException $exception) {
                    $this->discord->getLogger()->info($exception->getMessage());
                    $guild = $this->spud->guildRepository
                        ->findByPart($interaction->guild);

                    $forumChannel = new Channel();
                    $forumChannel->setGuild($guild);
                    $forumChannel->setDiscordId($formChannelPart->id);

                    $this->spud->channelRepository
                        ->save($forumChannel);
                }

                try {
                    $directoryChannel = $this->spud->channelRepository
                        ->findByPart($directoryChannelPart);
                } catch (\OutOfBoundsException $exception) {
                    $this->discord->getLogger()->info($exception->getMessage());
                    if (!isset($guild)) {
                        $guild = $this->spud->guildRepository
                            ->findByPart($interaction->guild);
                    }

                    $directoryChannel = new Channel();
                    $directoryChannel->setGuild($guild);
                    $directoryChannel->setDiscordId($directoryChannelPart->id);

                    $this->spud->channelRepository
                        ->save($directoryChannel);
                }

                try {
                    $directory = $directoryRepository->findByForumChannel($forumChannel);
                    $forumDirectoryPart = $interaction->guild
                        ->channels->get('id', $directory->getDirectoryChannel()->getDiscordId());

                    $directoryMessage = $directoryRepository
                        ->getEmbedContentFromPart($formChannelPart);

                    $embed = $this->spud->getSimpleResponseBuilder();
                    $embed->setTitle($formChannelPart->name . ' thread directory');
                    $embed->setDescription($directoryMessage);

                    $success = function (Message $message) use ($embed) {
                        $message->edit($embed->getEmbeddedMessage());
                    };

                    $rejected = function () use ($directoryChannelPart, $embed, $directory) {
                        $directoryChannelPart
                            ->sendMessage($embed->getEmbeddedMessage())->done(
                                function (Message $message) use ($directory) {
                                    $directory->setEmbedId($message->id);

                                    $this->spud->directoryRepository
                                        ->save($directory);
                                }
                            );
                    };

                    $forumDirectoryPart->messages->fetch($directory->getEmbedId())->done($success, $rejected);

                    $response->setDescription('Updated the directory.');
                } catch (\OutOfBoundsException $exception) {
                    $this->discord->getLogger()->info($exception->getMessage());

                    $directory = new Directory();
                    $directory->setDirectoryChannel($directoryChannel);
                    $directory->setForumChannel($forumChannel);

                    $directoryMessage = $directoryRepository->getEmbedContentFromPart($formChannelPart);
                    $embed = $this->spud->getSimpleResponseBuilder();
                    $embed->setTitle($formChannelPart->name . ' Directory');
                    $embed->setDescription($directoryMessage);

                    $directoryChannelPart->sendMessage($embed->getEmbeddedMessage())
                        ->done(function (Message $message) use ($directory) {
                            $directory->setEmbedId($message->id);

                            $this->spud->directoryRepository
                                ->save($directory);
                        }
                        );

                    $response->setDescription('Directory generated.');
                }
            } else {
                $response->setDescription('Unable to locate the specified forum channel.');
            }

            $interaction->respondWithMessage($response->getEmbeddedMessage(), true);
        };
    }

    public function getCommand(): Command
    {
        $channel = new Option($this->discord);
        $channel->setName('channel')
            ->setDescription('The forum channel.')
            ->setRequired(true)
            ->setType(Option::CHANNEL);

        $command = CommandBuilder::new();
        $command->setName($this->getName())
            ->setDescription($this->getDescription())
            ->addOption($channel);

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
