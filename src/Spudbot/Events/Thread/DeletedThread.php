<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023-2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Events\Thread;


use DI\Attribute\Inject;
use Discord\Parts\Channel\Message;
use Discord\Parts\Thread\Thread;
use Discord\WebSockets\Event;
use Spudbot\Interface\AbstractEventSubscriber;
use Spudbot\Services\ChannelService;
use Spudbot\Services\ThreadService;

class DeletedThread extends AbstractEventSubscriber
{
    #[Inject]
    protected ChannelService $channelService;
    #[Inject]
    protected ThreadService $threadService;

    public function getEventName(): string
    {
        return Event::THREAD_DELETE;
    }

    public function update(?Thread $threadPart = null): void
    {
        if (!$threadPart) {
            return;
        }


        try {
            $thread = $this->threadService->findWithPart($threadPart);
            $this->spud->threadRepository->remove($thread);
        } catch (\Exception $exception) {
            /**
             * Already deleted
             */
        }

        try {
            $forumChannel = $this->channelService->findWithPart($threadPart->parent);
        } catch (\OutOfBoundsException $exception) {
            /**
             * There is no forum channel or directory
             */
            return;
        }


        try {
            $directory = $this->spud->directoryRepository
                ->findByForumChannel($forumChannel);

            $forumDirectoryPart = $threadPart->guild->channels
                ->get('id', $directory->getDirectoryChannel()->getDiscordId());

            if (!$forumDirectoryPart) {
                throw new \BadMethodCallException('The specified directory channel does not exist.');
            }

            $directoryMessage = $this->spud->directoryRepository
                ->getEmbedContentFromPart($threadPart->parent);

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

                        $this->spud->directoryRepository
                            ->save($directory);
                    });
            };

            $forumDirectoryPart->messages->fetch($directory->getEmbedId())
                ->done($success, $rejected);
        } catch (\OutOfBoundsException $exception) {
            /**
             * There is no directory for this channel
             */
            return;
        }
    }

    public function canRun(?Thread $threadPart = null): bool
    {
        return $threadPart && isset($threadPart->id, $threadPart->guild, $threadPart->parent);
    }
}
