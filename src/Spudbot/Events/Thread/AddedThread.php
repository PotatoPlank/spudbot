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
use Spudbot\Parsers\DirectoryParser;
use Spudbot\Services\ChannelService;
use Spudbot\Services\DirectoryService;

class AddedThread extends AbstractEventSubscriber
{
    #[Inject]
    protected ChannelService $channelService;
    #[Inject]
    protected DirectoryService $directoryService;
    #[Inject]
    protected DirectoryParser $directoryParser;

    public function getEventName(): string
    {
        return Event::THREAD_CREATE;
    }

    public function update(?Thread $threadPart = null): void
    {
        if (!$threadPart) {
            return;
        }
        $forumChannel = $this->channelService->findOrCreateWithPart($threadPart->parent);


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
}
