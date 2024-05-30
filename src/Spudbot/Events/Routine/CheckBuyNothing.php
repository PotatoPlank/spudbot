<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023-2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Events\Routine;


use Carbon\Carbon;
use DI\Attribute\Inject;
use Discord\Helpers\Collection;
use Discord\Parts\Channel\Channel;
use Discord\Parts\Thread\Thread;
use Discord\Repository\Channel\ThreadRepository;
use Spudbot\Bot\Events;
use Spudbot\Events\AbstractEventSubscriber;
use Spudbot\Model\Guild;
use Spudbot\Model\Marketplace;
use Spudbot\Services\GuildService;
use Spudbot\Services\MarketplaceService;

use function React\Promise\all;

class CheckBuyNothing extends AbstractEventSubscriber
{
    private const GUILD_TARGET = '1114365923625816155';
    private const CHANNEL_TARGET = '1114434239660839043';
    protected static array $marketplaces = [];
    #[Inject]
    protected GuildService $guildService;
    #[Inject]
    protected MarketplaceService $marketplaceService;
    protected Collection $removableStatuses;

    public function getEventName(): string
    {
        return Events::EVERY_TEN_MINUTES->value;
    }

    public function update(): void
    {
        $guilds = $this->guildService->all(function (Guild $guild) {
            return !empty($guild->getChannelMarketplaceId()) && $guild->getDiscordId() === self::GUILD_TARGET;
        });
        foreach ($guilds as $guild) {
            $part = $this->spud->discord->guilds->get('id', $guild->getDiscordId());
            if (!$part) {
                continue;
            }
            $channel = $this->getMarketplaceChannel($part, $guild);
            if (!$channel) {
                continue;
            }

            if (!isset($this->removableStatuses)) {
                $this->removableStatuses = $channel->available_tags->filter(function ($tag) {
                    return in_array(strtolower($tag->name), ['taken', 'fulfilled'], true);
                });
            }

            $active = $channel->threads->freshen()
                ->then($this->onlyInMarketplace(...))
                ->then(function (Collection $threads) {
                    return $threads->filter(function (Thread $thread) {
                        return $this->shouldBeRemoved($thread);
                    });
                });
            $archived = $channel->threads->archived()
                ->then($this->onlyInMarketplace(...))
                ->then(function (Collection $threads) {
                    return $threads->filter(function (Thread $thread) {
                        return $this->shouldBeRemoved($thread) || $this->isAged($thread?->archive_timestamp);
                    });
                });
            all([$active, $archived])->then(function (array $promises) use ($guild, $part, $channel) {
                $threads = new Collection();
                /**
                 * @var Collection[] $promises
                 */
                foreach ($promises as $promise) {
                    $threads->merge($promise);
                }
                /**
                 * @var Thread[] $threads
                 */
                foreach ($threads as $thread) {
                    $shouldBeRemoved = $this->shouldBeRemoved($thread);
                    $hasAged = $this->isAged($thread?->archive_timestamp);
                    try {
                        $marketplace = $this->marketplaceService->findOrCreateWithPart($thread);
                        $marketplace->lastStatus = Marketplace::makeStatus($thread);
                        $marketplace->tags = Marketplace::makeTags($thread);
                        if ($this->shouldBeRemoved($thread)) {
                            $marketplace->taken();
                        }
                        $this->marketplaceService->save($marketplace);
                    } catch (\InvalidArgumentException $exception) {
                        // Saving marketplace without a member owner failed, don't track those.
                    }
                    $output = $guild->getChannelThreadPart(Guild::BOT_LOG_CHANNEL, $part);
                    $this->spud->interact()->setTitle("Removed $thread->name")
                        ->setDescription(
                            "Aged: " . ($hasAged ? 'true' : 'false') . PHP_EOL .
                            "Taken: " . ($shouldBeRemoved ? 'true' : 'false') . PHP_EOL
                        )
                        ->sendTo($output);
                    $channel->threads->delete($thread);
                }
                exit;
            });
        }
    }

    protected function getMarketplaceChannel(\Discord\Parts\Guild\Guild $part, Guild $guild): ?Channel
    {
        if (!isset(self::$marketplaces[$guild->getDiscordId()])) {
            $channel = $guild->getChannelThreadPart(Guild::MARKETPLACE_CHANNEL, $part);
            self::$marketplaces[$guild->getDiscordId()] = $channel;
        }
        return self::$marketplaces[$guild->getDiscordId()];
    }

    protected function shouldBeRemoved(Thread $thread, bool $removeNoTags = false): bool
    {
        if ($thread->applied_tags === null) {
            return $removeNoTags;
        }
        foreach ($thread->applied_tags as $tag) {
            $hasStatus = $this->removableStatuses->find(function ($status) use ($tag) {
                return $status->id === $tag;
            });
            if ($hasStatus !== null) {
                return true;
            }
        }
        return false;
    }

    protected function isAged(?Carbon $archiveTimestamp, bool $removeNullTimestamps = false): bool
    {
        if ($archiveTimestamp === null) {
            return $removeNullTimestamps;
        }
        return $archiveTimestamp->diffInDays(Carbon::now()) >= 60;
    }

    protected function onlyInMarketplace(ThreadRepository|Collection $threads): Collection
    {
        $marketplace = self::$marketplaces[$threads->first()->guild_id] ?? -99;
        return $threads->filter(function ($thread) use ($marketplace) {
            return $thread?->parent_id === $marketplace->id;
        });
    }
}
