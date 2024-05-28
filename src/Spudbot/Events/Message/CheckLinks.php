<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023-2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Events\Message;


use Carbon\Carbon;
use DI\Attribute\Inject;
use Discord\Parts\Channel\Message;
use Discord\WebSockets\Event;
use Spudbot\Events\AbstractEventSubscriber;
use Spudbot\Model\Guild;
use Spudbot\Services\GuildService;
use Spudbot\Util\DiscordFormatter;

class CheckLinks extends AbstractEventSubscriber
{
    #[Inject]
    protected GuildService $guildService;

    public function getEventName(): string
    {
        return Event::MESSAGE_CREATE;
    }

    public function update(?Message $message = null): void
    {
        if (!$message) {
            return;
        }

        $content = strtolower($message->content);
        if (str_contains($content, 'discord.gg')) {
            $message->delete();
            $guild = $this->guildService->findOrCreateWithPart($message->guild);
            $output = $guild->getChannelThreadPart(Guild::BOT_LOG_CHANNEL, $message->guild);
            $channelTag = DiscordFormatter::mentionChannel($message->channel_id);

            $alert = "New member {$message->member->displayname} attempted to post {$content}." . PHP_EOL;
            $alert .= "This message was removed automatically from {$channelTag}.";

            $this->spud->interact()
                ->setTitle('Discord Link Removed')
                ->setDescription($alert)
                ->sendTo($output);
        }
    }

    public function canRun(?Message $message = null): bool
    {
        if (!$message) {
            return false;
        }
        return $message->member && ($message->member->joined_at?->diffInDays(Carbon::now()) ?? -99) <= 10;
    }
}
