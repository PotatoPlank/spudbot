<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Bindable\Command;

use Discord\Builders\CommandBuilder;
use Discord\Parts\Channel\Channel;
use Discord\Parts\Interactions\Command\Command;
use Discord\Parts\Interactions\Command\Option;
use Discord\Parts\Interactions\Interaction;
use Spudbot\Interface\IBindableCommand;
use Spudbot\Model\Thread;

class TagThread extends IBindableCommand
{
    protected string $name = 'tag_thread';
    protected string $description = 'Tag a thread with a category, used with directories.';

    public function getListener(): callable
    {
        return function (Interaction $interaction) {
            $builder = $this->spud->getSimpleResponseBuilder();
            $tag = $interaction->data->options['tag']->value;

            $builder->setTitle('Applied Tag');
            $builder->setDescription("You do not have correct permissions to apply the tag.");

            $threadTypes = [
                Channel::TYPE_PUBLIC_THREAD,
                Channel::TYPE_PRIVATE_THREAD,
                Channel::TYPE_ANNOUNCEMENT_THREAD
            ];

            var_dump($interaction->guild->channels->get('id', $interaction->channel_id));

            $isThread = isset($interaction->channel->parent_id);
            $isOwner = isset($interaction->channel->owner_id) && $interaction->channel->owner_id === $interaction->member->id;
            $isMod = $interaction->member->getPermissions()->manage_messages;
            if ($isThread && ($isOwner || $isMod)) {
                $guild = $this->spud->getGuildRepository()->findByPart($interaction->guild);
                try {
                    $channel = $this->spud->getChannelRepository()
                        ->findByDiscordId($interaction->channel->parent_id, $interaction->guild->id);
                } catch (\OutOfBoundsException $exception) {
                    $channel = new \Spudbot\Model\Channel();
                    $channel->setGuild($guild);
                    $channel->setDiscordId($interaction->channel->parent_id);
                    $this->spud->getChannelRepository()
                        ->save($channel);
                }

                try {
                    $thread = $this->spud->getThreadRepository()
                        ->findByDiscordId($interaction->channel_id, $interaction->guild->id);
                    $thread->setTag($tag);
                } catch (\OutOfBoundsException $exception) {
                    $thread = new Thread();
                    $thread->setGuild($guild);
                    $thread->setChannel($channel);
                    $thread->setTag($tag);
                    $thread->setDiscordId($interaction->channel);
                }

                $this->spud->getThreadRepository()
                    ->save($thread);
                $builder->setDescription("Your tag {$tag} was applied.");
            }

            $interaction->respondWithMessage($builder->getEmbeddedMessage());
        };
    }

    public function getCommand(): Command
    {
        $tag = new Option($this->discord);
        $tag->setName('tag')
            ->setDescription('The tag name.')
            ->setRequired(true)
            ->setType(Option::STRING);

        $command = CommandBuilder::new();
        $command->setName($this->getName())
            ->setDescription($this->getDescription())
            ->addOption($tag);

        return new Command($this->discord, $command->toArray());
    }
}