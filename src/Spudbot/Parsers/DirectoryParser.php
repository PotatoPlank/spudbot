<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Parsers;

use Discord\Parts\Channel\Channel;
use Spudbot\Model\Thread;
use Spudbot\Services\ThreadService;
use Spudbot\Util\DiscordFormatter;

class DirectoryParser
{
    private const MESSAGE_NO_THREADS_FOUND = 'No threads found.';
    private const DEFAULT_CATEGORY = 'General';
    /**
     * @var array[] $categories
     */
    protected array $categories;
    protected int $threadCount = 0;

    public function __construct(protected ThreadService $threadService)
    {
        $this->fresh();
    }

    protected function fresh(): void
    {
        $this->categories = [
            self::DEFAULT_CATEGORY => [],
        ];
        $this->threadCount = 0;
    }

    public function fromPart(Channel $forumChannel): self
    {
        $this->fresh();
        $this->threadCount = $forumChannel->threads->count();
        if ($forumChannel->threads->count() === 0) {
            throw new \InvalidArgumentException('The provided forum channel does not have any threads.');
        }

        /**
         * @var \Discord\Parts\Thread\Thread $threadPart
         */
        foreach ($forumChannel->threads as $threadPart) {
            if (!$this->isThreadEligible($threadPart)) {
                continue;
            }
            $thread = $this->threadService->findOrCreateWithPart($threadPart);
            $this->addThread($thread);
        }

        return $this;
    }

    protected function isThreadEligible(\Discord\Parts\Thread\Thread $thread): bool
    {
        $threadIsLocked = $thread->locked;
        $threadRecentlyArchived = $thread->archived && $thread->archive_timestamp->diffInWeeks() < 2;
        return !$threadIsLocked && (!$thread->archived || $threadRecentlyArchived);
    }

    public function addThread(Thread $thread): void
    {
        $name = empty($thread->getTag()) ? self::DEFAULT_CATEGORY : $thread->getTag();
        $id = $thread->getDiscordId();
        $this->addCategory($name, $id);
    }

    protected function addCategory(string $category, string $threadDiscordId): void
    {
        if (!isset($this->categories[$category])) {
            $this->categories[$category] = [];
        }
        $this->categories[$category][] = $threadDiscordId;
    }

    public function getBody(): string
    {
        if (empty($this->categories)) {
            return self::MESSAGE_NO_THREADS_FOUND;
        }

        $body = '';
        foreach ($this->categories as $category => $threads) {
            if (empty($threads)) {
                continue;
            }
            $body .= DiscordFormatter::boldText($category) . PHP_EOL;
            foreach ($threads as $threadId) {
                $body .= DiscordFormatter::mentionChannel($threadId) . PHP_EOL;
            }
            $body .= PHP_EOL;
        }

        return !empty($body) ? $body : self::MESSAGE_NO_THREADS_FOUND;
    }

    public function getCategories(): array
    {
        return array_keys($this->categories);
    }
}
