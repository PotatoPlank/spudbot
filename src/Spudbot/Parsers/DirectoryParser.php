<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Parsers;

use Discord\Parts\Channel\Channel;
use Discord\Parts\Thread\Thread;
use InvalidArgumentException;
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
            throw new InvalidArgumentException('The provided forum channel does not have any threads.');
        }

        $threadList = $forumChannel->threads->toArray();
        // Sort threads first
        usort($threadList, function ($a, $b) {
            return strcasecmp($a->name, $b->name);
        });

        /**
         * @var Thread $thread
         */

        foreach ($threadList as $thread) {
            if (!$this->isThreadEligible($thread)) {
                continue;
            }
            $tagList = $thread->applied_tags;
            $tagName = self::DEFAULT_CATEGORY;
            if (empty($tagList)) {
                $this->addThread($thread, $tagName);
                continue;
            }
            foreach ($tagList as $tagId) {
                $tag = $thread->parent->available_tags->get('id', $tagId);
                $tagName = $tag?->name ?? '';
                $this->addThread($thread, $tagName);
            }
        }

        return $this;
    }

    protected function isThreadEligible(Thread $thread): bool
    {
        $threadIsLocked = $thread->locked;
        $threadRecentlyArchived = $thread->archived && $thread->archive_timestamp->diffInWeeks() < 2;
        return !$threadIsLocked && (!$thread->archived || $threadRecentlyArchived);
    }

    public function addThread(Thread $thread, ?string $categoryName): void
    {
        $name = empty($categoryName) ? self::DEFAULT_CATEGORY : $categoryName;
        $this->addCategory($name, $thread->id);
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

        ksort($this->categories);

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

}
