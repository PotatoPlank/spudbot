<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Services;

use OutOfBoundsException;
use Spudbot\Model\Thread;
use Spudbot\Repositories\ThreadRepository;

class ThreadService
{
    public function __construct(
        public ThreadRepository $threadRepository,
        public GuildService $guildService,
        public ChannelService $channelService
    ) {
    }

    public function findOrCreateWithPart(\Discord\Parts\Thread\Thread $thread): Thread
    {
        try {
            return $this->threadRepository->findByPart($thread);
        } catch (OutOfBoundsException $exception) {
            return $this->threadRepository->save(Thread::create([
                'discordId' => $thread->id,
                'guild' => $this->guildService->findOrCreateWithPart($thread->guild),
                'channel' => $this->channelService->findOrCreateWithPart($thread->parent),
                'tag' => '',
            ]));
        }
    }

    public function save(Thread $thread): Thread
    {
        return $this->threadRepository->save($thread);
    }

    public function remove(Thread $thread): bool
    {
        return $this->threadRepository->remove($thread);
    }

    public function findWithDiscordId(string $discordId, string $discordGuildId): Thread
    {
        try {
            return $this->threadRepository->findByDiscordId($discordId, $discordGuildId);
        } catch (OutOfBoundsException $exception) {
            return Thread::create([
                'discordId' => $discordId,
                'tag' => '',
            ]);
        }
    }
}
