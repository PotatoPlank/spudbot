<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023-2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Events\Routine;


use Discord\Helpers\Collection;
use Discord\Parts\Thread\Thread;
use Spudbot\Bot\Events;
use Spudbot\Events\AbstractEventSubscriber;

class CheckBuyNothing extends AbstractEventSubscriber
{
    private const GUILD_TARGET = '1114365923625816155';
    private const CHANNEL_TARGET = '1114434239660839043';
    private Collection $removableStatuses;

    public function getEventName(): string
    {
        return Events::EVERY_HALF_MINUTE->value;
    }

    public function update(): void
    {
        $channel = $this->spud->discord->guilds->get('id', self::GUILD_TARGET)
            ->channels->get('id', self::CHANNEL_TARGET);
        if (!$channel) {
            return;
        }

        if (!isset($this->removableStatuses)) {
            $this->removableStatuses = $channel->available_tags->filter(function ($tag) {
                return in_array(strtolower($tag->name), ['taken', 'fulfilled'], true);
            });
        }


        $channel->threads->active()->done(function (object $threads) {
            /**
             * @var Thread $thread
             */
            foreach ($threads as $thread) {
                var_dump($this->shouldBeRemoved($thread));
            }
        });
        $channel->threads->archived()->done(function (object $threads) {
            /**
             * @var Thread $thread
             */
            foreach ($threads as $thread) {
                var_dump($this->shouldBeRemoved($thread));
            }
        });
//        foreach ( as $thread) {
//            var_dump($this->shouldBeRemoved($thread));
//        }
//        exit;
    }

    protected function shouldBeRemoved(Thread $thread): bool
    {
        if ($thread->applied_tags === null) {
            return true;
        }
        foreach ($thread->applied_tags as $tag) {
            $hasStatus = $this->removableStatuses->find(function ($status) use ($tag) {
                return $status->id === $tag->id;
            });
            if ($hasStatus !== null) {
                return true;
            }
        }
        return false;
    }
}
