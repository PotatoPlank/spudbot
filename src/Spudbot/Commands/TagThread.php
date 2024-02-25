<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023-2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Commands;

use Discord\Builders\CommandBuilder;
use Discord\Parts\Channel\Channel;
use Discord\Parts\Channel\Message;
use Discord\Parts\Interactions\Command\Command;
use Discord\Parts\Interactions\Command\Option;
use Discord\Parts\Interactions\Interaction;
use Spudbot\Interface\AbstractCommandSubscriber;
use Spudbot\Model\Thread;

class TagThread extends AbstractCommandSubscriber
{
    public function update(?Interaction $interaction = null): void
    {
        if (!$interaction) {
            return;
        }
        $builder = $this->spud->getSimpleResponseBuilder();
        $tag = $interaction->data->options['tag']->value;
        $threadId = $interaction->data->options['thread']->value;

        $channelPart = $interaction->guild->channels->find(function (Channel $channel) use ($threadId) {
            return !empty($channel->threads->get('id', $threadId));
        });

        if (!$channelPart) {
            $builder->setTitle('Error');
            $builder->setDescription("Unable to locate the relevant parent channel.");
            $interaction->respondWithMessage($builder->getEmbeddedMessage(), true);
            return;
        }

        $threadOwner = $interaction->channel->owner_id ?? null;
        $isOwner = $threadOwner === $interaction->member->id;
        $isMod = $interaction->member->getPermissions()->manage_messages;

        if (!$isOwner && !$isMod) {
            $builder->setTitle('Error');
            $builder->setDescription("You do not have correct permissions to apply the tag.");
            $interaction->respondWithMessage($builder->getEmbeddedMessage(), true);
            return;
        }

        $builder->setTitle('Applied Tag');

        $guild = $this->spud->guildRepository->findByPart($interaction->guild);
        try {
            $channel = $this->spud->channelRepository
                ->findByDiscordId($channelPart->id, $channelPart->guild_id);
        } catch (\OutOfBoundsException $exception) {
            $channel = new \Spudbot\Model\Channel();
            $channel->setGuild($guild);
            $channel->setDiscordId($channelPart->id);
            $this->spud->channelRepository
                ->save($channel);
        }

        try {
            $thread = $this->spud->threadRepository
                ->findByDiscordId($threadId, $interaction->guild->id);
            $thread->setTag($tag);
        } catch (\OutOfBoundsException $exception) {
            $thread = new Thread();
            $thread->setGuild($guild);
            $thread->setChannel($channel);
            $thread->setTag($tag);
            $thread->setDiscordId($interaction->channel);
        }

        $this->spud->threadRepository
            ->save($thread);
        $builder->setDescription("Your tag {$tag} was applied.");

        try {
            $directory = $this->spud->directoryRepository
                ->findByForumChannel($channel);

            $forumDirectoryPart = $channelPart->guild->channels
                ->get('id', $directory->getDirectoryChannel()->getDiscordId());

            if ($forumDirectoryPart) {
                $directoryMessage = $this->spud->directoryRepository
                    ->getEmbedContentFromPart($channelPart);

                $embed = $this->spud->getSimpleResponseBuilder();
                $embed->setTitle($forumDirectoryPart->name . ' thread directory');
                $embed->setDescription($directoryMessage);

                $success = function (Message $message) use ($embed) {
                    $message->edit($embed->getEmbeddedMessage());
                };

                $rejected = function () use ($forumDirectoryPart, $embed, $directory) {
                    $forumDirectoryPart
                        ->sendMessage($embed->getEmbeddedMessage())->done(
                            function (Message $message) use ($directory) {
                                $directory->setEmbedId($message->id);

                                $this->spud->directoryRepository
                                    ->save($directory);
                            }
                        );
                };

                $forumDirectoryPart->messages
                    ->fetch($directory->getEmbedId())->done($success, $rejected);
            } else {
                throw new \RuntimeException('The specified directory channel does not exist.');
            }
        } catch (\OutOfBoundsException $exception) {
            /**
             * There is no directory for this channel
             */
        }

        $interaction->respondWithMessage($builder->getEmbeddedMessage(), true);
    }

    public function getCommand(): Command
    {
        $tag = new Option($this->spud->discord);
        $thread = new Option($this->spud->discord);

        $thread->setName('thread')
            ->setDescription('The thread to tag.')
            ->setRequired(true)
            ->setType(Option::CHANNEL);

        $tag->setName('tag')
            ->setDescription('The tag name.')
            ->setRequired(true)
            ->setType(Option::STRING);

        $command = CommandBuilder::new();
        $command->setName($this->getCommandName())
            ->setDescription($this->getCommandDescription())
            ->addOption($thread)
            ->addOption($tag);

        return new Command($this->spud->discord, $command->toArray());
    }

    public function getCommandName(): string
    {
        return 'tag_thread';
    }

    public function getCommandDescription(): string
    {
        return 'Tag a thread with a category, used with directories.';
    }
}
