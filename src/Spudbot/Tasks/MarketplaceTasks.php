<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Tasks;

use Carbon\Carbon;
use Discord\Helpers\Collection;
use Discord\Parts\Channel\Channel;
use Discord\Parts\Thread\Thread;
use Discord\Repository\Channel\ThreadRepository;

class MarketplaceTasks
{
    /**
     * @var Collection[] $removableTags
     */
    protected static array $removableTags = [];

    public static function getRemovableThreads(
        ThreadRepository|Collection $threads,
        bool $removeNoTags = false
    ): Collection {
        return $threads->filter(function (Thread $thread) use ($removeNoTags) {
            return self::isThreadRemovable($thread, $removeNoTags);
        });
    }

    public static function isThreadRemovable(Thread $thread, bool $removeNoTags = false): bool
    {
        if ($thread->applied_tags === null) {
            return $removeNoTags;
        }
        foreach ($thread->applied_tags as $tag) {
            $hasStatus = self::getRemovableTags($thread->guild_id, $thread->parent)
                ->find(function ($status) use ($tag) {
                    return $status->id === $tag;
                });
            if ($hasStatus !== null) {
                return true;
            }
        }
        return false;
    }

    protected static function getRemovableTags(string $guildId, ?Channel $channel = null): Collection
    {
        self::$removableTags[$guildId] ??= new Collection();
        if ($channel !== null && self::$removableTags[$guildId]->count() === 0) {
            self::$removableTags[$guildId] = $channel->available_tags->filter(function ($tag) {
                return in_array(strtolower($tag->name), ['taken', 'fulfilled'], true);
            });
        }
        return self::$removableTags[$guildId];
    }

    public static function getAgedThreads(
        ThreadRepository|Collection $threads,
        bool $removeNull = false,
        int $ageLimit = 60
    ) {
        return $threads->filter(function (Thread $thread) use ($removeNull, $ageLimit) {
            return self::isThreadAged($thread, $removeNull, $ageLimit);
        });
    }

    public static function isThreadAged(Thread $thread, bool $removeNull = false, int $ageLimit = 60): bool
    {
        if ($thread?->archive_timestamp === null) {
            return $removeNull;
        }
        return $thread->archive_timestamp->diffInDays(Carbon::now()) >= $ageLimit;
    }

    public static function getAgedOrRemovableThreads(ThreadRepository|Collection $threads)
    {
        return $threads->filter(function (Thread $thread) {
            return self::isThreadRemovable($thread) || self::isThreadAged($thread);
        });
    }

    public static function isInChannel(ThreadRepository|Collection $threads, Channel $channel): Collection
    {
        return $threads->filter(function (Thread $thread) use ($channel) {
            return $thread->parent_id === $channel->id;
        });
    }

    public static function hasMemberOwner(Thread $thread): bool
    {
        return $thread?->owner_member !== null;
    }
}
