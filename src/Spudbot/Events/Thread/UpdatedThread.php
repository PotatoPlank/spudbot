<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023-2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Events\Thread;


use BadMethodCallException;
use DI\Attribute\Inject;
use Discord\Parts\Channel\Message;
use Discord\Parts\Thread\Thread;
use Discord\WebSockets\Event;
use OutOfBoundsException;
use Spudbot\Events\AbstractEventSubscriber;
use Spudbot\Model\Guild;
use Spudbot\Parsers\DirectoryParser;
use Spudbot\Services\ChannelService;
use Spudbot\Services\DirectoryService;
use Spudbot\Services\MarketplaceService;
use Spudbot\Tasks\MarketplaceTasks;

class UpdatedThread extends AbstractEventSubscriber
{
    #[Inject]
    protected ChannelService $channelService;
    #[Inject]
    protected DirectoryService $directoryService;
    #[Inject]
    protected MarketplaceService $marketplaceService;
    #[Inject]
    protected DirectoryParser $directoryParser;

    public function getEventName(): string
    {
        return Event::THREAD_UPDATE;
    }

    public function update(?Thread $threadPart = null): void
    {
        if (!$threadPart) {
            return;
        }

        try {
            $forumChannel = $this->channelService->findOrCreateWithPart($threadPart->parent);
            $this->saveMarketplace($threadPart, $forumChannel->getGuild());
        } catch (OutOfBoundsException $exception) {
            /**
             * There is no forum channel or directory
             */
            return;
        }


        try {
            $directory = $this->directoryService
                ->findWithForumChannel($forumChannel);
            if (!$directory) {
                throw new OutOfBoundsException('Unable to find directory.');
            }

            $forumDirectoryPart = $threadPart->guild->channels
                ->get('id', $directory->getDirectoryChannel()->getDiscordId());

            if (!$forumDirectoryPart) {
                throw new BadMethodCallException('The specified directory channel does not exist.');
            }

            $directoryMessage = $this->directoryParser->fromPart($threadPart->parent)
                ->getBody();


            $embed = $this->spud->interact()
                ->setTitle("{$threadPart->parent->name} thread directory")
                ->setDescription($directoryMessage);

            $success = function (Message $message) use ($embed) {
                $message->edit($embed->build());
            };

            $rejected = function () use ($forumDirectoryPart, $embed, $directory) {
                $embed->sendTo($forumDirectoryPart)
                    ->done(function (Message $message) use ($directory) {
                        $directory->setEmbedId($message->id);

                        $this->directoryService
                            ->save($directory);
                    });
            };

            $forumDirectoryPart->messages->fetch($directory->getEmbedId())
                ->done($success, $rejected);
        } catch (OutOfBoundsException $exception) {
            /**
             * There is no directory for this channel
             */
            return;
        }
    }

    protected function saveMarketplace(Thread $thread, Guild $guild): void
    {
        if (!$guild->hasMarketplace() || !MarketplaceTasks::hasMemberOwner($thread)) {
            return;
        }
        $part = $this->spud->discord->guilds->get('id', $guild->getDiscordId());
        if ($part === null) {
            return;
        }
        $channel = $guild->getChannelThreadPart(Guild::MARKETPLACE_CHANNEL, $part);
        if ($channel->id !== $thread->parent_id) {
            return;
        }
        $marketplace = $this->marketplaceService->findOrCreateWithPart($thread);
        $marketplace->name = $thread->name;
        $marketplace->lastStatus = $marketplace::makeStatus($thread);
        $marketplace->tags = $marketplace::makeTags($thread);
        $this->marketplaceService->save($marketplace);
    }
}
