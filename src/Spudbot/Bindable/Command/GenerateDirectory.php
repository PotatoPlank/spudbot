<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Bindable\Command;

use Discord\Builders\CommandBuilder;
use Discord\Builders\MessageBuilder;
use Discord\Parts\Channel\Message;
use Discord\Parts\Interactions\Command\Command;
use Discord\Parts\Interactions\Command\Option;
use Discord\Parts\Interactions\Interaction;
use Spudbot\Interface\IBindableCommand;
use Spudbot\Model\Directory;

class GenerateDirectory extends IBindableCommand
{
    protected string $name = 'forum_directory';
    protected string $description = 'Creates a generated forum directory from the specified channel.';

    public function getListener(): callable
    {
        return function (Interaction $interaction) {
            $directoryRepository = $this->spud->getDirectoryRepository();
            $embed = $this->spud->getSimpleResponseBuilder();
            $embed->setTitle('Forum Directory');

            $targetChannelId = $interaction->data->options['channel']->value;
            $targetChannel = $interaction->guild->channels->get('id', $targetChannelId);
            $embedChannel = $interaction->channel;


            if ($targetChannel) {
                try {
                    $forumChannel = $this->spud->getChannelRepository()->findByPart($targetChannel);
                } catch (\OutOfBoundsException $exception) {
                    $guild = $this->spud->getGuildRepository()->findByPart($interaction->guild);
                    $forumChannel = new \Spudbot\Model\Channel();
                    $forumChannel->setGuild($guild);
                    $forumChannel->setDiscordId($targetChannel->id);
                    $this->spud->getChannelRepository()->save($forumChannel);
                }

                try {
                    $directoryChannel = $this->spud->getChannelRepository()->findByPart($embedChannel);
                } catch (\OutOfBoundsException $exception) {
                    if (!isset($guild)) {
                        $guild = $this->spud->getGuildRepository()->findByPart($interaction->guild);
                    }
                    $directoryChannel = new \Spudbot\Model\Channel();
                    $directoryChannel->setGuild($guild);
                    $directoryChannel->setDiscordId($embedChannel->id);
                    $this->spud->getChannelRepository()->save($directoryChannel);
                }

                try {
                    $directory = $directoryRepository->findByForumChannel($forumChannel);
                    $directoryPart = $interaction->guild->channels->get(
                        'id',
                        $directory->getDirectoryChannel()->getDiscordId()
                    );

                    $content = $directoryRepository->getEmbedContentFromPart(
                        $targetChannel
                    );
                    $success = function (Message $message) use ($content) {
                        $message->edit(MessageBuilder::new()->setContent($content));
                    };

                    $rejected = function () use ($embedChannel, $content, $directory, $directoryRepository) {
                        $embedChannel->sendMessage($content)->done(
                            function (Message $message) use ($directory, $directoryRepository) {
                                $directory->setEmbedId($message->id);
                                $directoryRepository->save($directory);
                            }
                        );
                    };

                    $directoryPart->messages->fetch($directory->getEmbedId())->done($success, $rejected);
                    $embed->setDescription('Updated the directory.');
                } catch (\OutOfBoundsException $exception) {
                    $this->discord->getLogger()->info($exception->getMessage());

                    $directory = new Directory();
                    $directory->setDirectoryChannel($directoryChannel);
                    $directory->setForumChannel($forumChannel);

                    $content = $directoryRepository->getEmbedContentFromPart($targetChannel);
                    $embed->setDescription('Directory generated.');
                    $embedChannel->sendMessage($content)->done(
                        function (Message $message) use ($directory) {
                            $directory->setEmbedId($message->id);
                            $this->spud->getDirectoryRepository()->save($directory);
                        }
                    );
                }
            } else {
                $embed->setDescription('Unable to locate the specified forum channel.');
            }
            $interaction->respondWithMessage($embed->getEmbeddedMessage());
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