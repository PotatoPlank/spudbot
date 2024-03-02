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
use Spudbot\Services\ChannelService;

class GenerateDirectory extends AbstractCommandSubscriber
{
    #[Inject]
    protected ChannelService $channelService;

    public function update(?Interaction $interaction = null): void
    {
        if (!$interaction) {
            return;
        }

        $response = $this->spud->interact()
            ->setTitle('Forum Directory');


        $formChannelId = $interaction->data->options['channel']->value;
        $formChannelPart = $interaction->guild->channels->get('id', $formChannelId);
        $directoryChannelPart = $interaction->channel;

        if (!$formChannelPart) {
            $response->error('Unable to locate the specified forum channel')
                ->respondTo($interaction);
            return;
        }

        $forumChannel = $this->channelService->findWithPart($formChannelPart);
        $directoryChannel = $this->channelService->findWithPart($directoryChannelPart);

        try {
            $directory = $this->spud->directoryRepository->findByForumChannel($forumChannel);
            $forumDirectoryPart = $interaction->guild
                ->channels->get('id', $directory->getDirectoryChannel()->getDiscordId());

            if (!$forumDirectoryPart) {
                $response->error('Unable to locate the specified forum directory')
                    ->respondTo($interaction);
                return;
            }

            $directoryMessage = $this->spud->directoryRepository
                ->getEmbedContentFromPart($formChannelPart);

            $embed = $this->spud->interact()
                ->setTitle($formChannelPart->name . ' thread directory')
                ->setDescription($directoryMessage);

            $success = function (Message $message) use ($embed) {
                $message->edit($embed->build());
            };

            $rejected = function () use ($directoryChannelPart, $embed, $directory) {
                $directoryChannelPart->sendMessage($embed->build())
                    ->done(function (Message $message) use ($directory) {
                        $directory->setEmbedId($message->id);

                        $this->spud->directoryRepository
                            ->save($directory);
                    });
            };

            $forumDirectoryPart->messages->fetch($directory->getEmbedId())->done($success, $rejected);

            $response->setDescription('Updated the directory.');
        } catch (\OutOfBoundsException $exception) {
            $this->spud->discord->getLogger()->info($exception->getMessage());

            $directory = new Directory();
            $directory->setDirectoryChannel($directoryChannel);
            $directory->setForumChannel($forumChannel);

            $directoryMessage = $this->spud->directoryRepository->getEmbedContentFromPart($formChannelPart);
            $embed = $this->spud->interact()
                ->setTitle($formChannelPart->name . ' Directory')
                ->setDescription($directoryMessage);

            $directoryChannelPart->sendMessage($embed->build())
                ->done(function (Message $message) use ($directory) {
                    $directory->setEmbedId($message->id);

                    $this->spud->directoryRepository
                        ->save($directory);
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
