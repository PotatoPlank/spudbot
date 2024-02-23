<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023-2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Bindable\Event\Thread;


use Discord\Parts\Channel\Message;
use Discord\Parts\Thread\Thread;
use Discord\WebSockets\Event;
use Spudbot\Interface\IBindableEvent;

class DeletedThread extends IBindableEvent
{

    public function getBoundEvent(): string
    {
        return Event::THREAD_DELETE;
    }

    public function getListener(): callable
    {
        return function (?Thread $threadPart) {
            // See SPUDBOT-23
            return;
            try {
                $thread = $this->spud->threadRepository > findByPart($threadPart);
                $this->spud->threadRepository->remove($thread);
            } catch (\Exception $exception) {
                /**
                 * Already deleted
                 */
            }

            if ($threadPart) {
                try {
                    $forumChannel = $this->spud->channelRepository
                        ->findByPart($threadPart->parent);
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

                    if ($forumDirectoryPart) {
                        $directoryMessage = $this->spud->directoryRepository
                            ->getEmbedContentFromPart($threadPart->parent);

                        $embed = $this->spud->getSimpleResponseBuilder();
                        $embed->setTitle($threadPart->parent->name . ' thread directory');
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
                    return;
                }
            }
        };
    }
}
