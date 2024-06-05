<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023-2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Events\Routine;


use DI\Attribute\Inject;
use Discord\Helpers\Collection;
use Discord\Parts\Channel\Channel;
use Discord\Parts\Thread\Thread;
use Spudbot\Bot\Events;
use Spudbot\Events\AbstractEventSubscriber;
use Spudbot\Model\Guild;
use Spudbot\Model\Marketplace;
use Spudbot\Services\GuildService;
use Spudbot\Services\MarketplaceService;
use Spudbot\Tasks\MarketplaceTasks;

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
            return $guild->hasMarketplace() && $guild->discordId === self::GUILD_TARGET;
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

            $active = $channel->threads->active()
                ->then(fn($threads) => MarketplaceTasks::isInChannel($threads, $channel))
                ->then(fn($threads) => MarketplaceTasks::getRemovableThreads($threads));

            $archived = $channel->threads->archived()
                ->then(fn($threads) => MarketplaceTasks::isInChannel($threads, $channel))
                ->then(fn($threads) => MarketplaceTasks::getAgedOrRemovableThreads($threads));

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
                    $shouldBeRemoved = MarketplaceTasks::isThreadRemovable($thread);
                    $hasAged = MarketplaceTasks::isThreadAged($thread);
                    if (MarketplaceTasks::hasMemberOwner($thread)) {
                        $marketplace = $this->marketplaceService->findWithPart($thread);
                        if ($marketplace) {
                            $marketplace->lastStatus = Marketplace::makeStatus($thread);
                            $marketplace->tags = Marketplace::makeTags($thread);
                            if ($shouldBeRemoved) {
                                $marketplace->taken();
                            }
                            $this->marketplaceService->save($marketplace);
                        }
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
}
