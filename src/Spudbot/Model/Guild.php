<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023-2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

declare(strict_types=1);

namespace Spudbot\Model;

use BadMethodCallException;
use Carbon\CarbonTimeZone;
use Discord\Discord;
use Discord\Parts\Channel\Channel;

class Guild extends AbstractModel
{
    private string $discordId;
    private ?string $channelAnnounceId = null;
    private ?string $channelThreadAnnounceId = null;
    private CarbonTimeZone $timeZone;

    public function __construct()
    {
        $this->timeZone = new CarbonTimeZone('America/New_York');
    }

    public static function updateMemberCount(\Discord\Parts\Guild\Guild $guild, Discord $discord): void
    {
        $categoryName = 'Member Count 📈';

        $memberCount = $guild->member_count;
        $category = $guild->channels->get('name', $categoryName);
        if (!$category) {
            $category = new Channel($discord);
            $category->type = Channel::TYPE_CATEGORY;
            $category->name = $categoryName;
            $guild->channels->save($category);
        }
        $channel = $guild->channels->get('parent_id', $category->id);
        if (!$channel) {
            $everyoneRole = $guild->roles->get('name', '@everyone');
            $channel = new Channel($discord);
            $channel->type = Channel::TYPE_VOICE;
            $channel->name = "Member Count: {$memberCount}";
            if ($everyoneRole) {
                $channel->setPermissions($everyoneRole, [
                    'view_channel',
                ], [
                    'connect',
                ]);
            }
            $channel->parent_id = $category->id;
        } else {
            $channel->name = "Member Count: {$memberCount}";
        }

        $guild->channels->save($channel);
    }

    public function getOutputLocationId(): ?string
    {
        if (!empty($this->getChannelThreadAnnounceId())) {
            return $this->getChannelThreadAnnounceId();
        }
        return $this->getChannelAnnounceId();
    }

    public function getChannelThreadAnnounceId(): ?string
    {
        return $this->channelThreadAnnounceId;
    }

    public function setChannelThreadAnnounceId(?string $threadId): void
    {
        $this->channelThreadAnnounceId = $threadId;
    }

    public function getChannelAnnounceId(): ?string
    {
        return $this->channelAnnounceId;
    }

    public function setChannelAnnounceId(?string $channelId): void
    {
        $this->channelAnnounceId = $channelId;
    }

    public function getOutputPart(\Discord\Parts\Guild\Guild $guild): Channel
    {
        $channelId = $this->getChannelAnnounceId();
        $threadId = $this->getChannelThreadAnnounceId();

        $outputPart = $guild->channels->get('id', $channelId);
        if (!$outputPart) {
            throw new BadMethodCallException(
                "Failed locating channel {$channelId} for {$guild->id}."
            );
        }
        if ($this->isOutputLocationThread()) {
            $outputPart = $outputPart->threads->get('id', $threadId);
            if (!$outputPart) {
                throw new BadMethodCallException(
                    "Failed locating thread {$threadId} in channel {$channelId} for {$guild->id}"
                );
            }
        }
        return $outputPart;
    }

    public function isOutputLocationThread(): bool
    {
        return !empty($this->getChannelThreadAnnounceId());
    }

    /**
     * @return CarbonTimeZone
     */
    public function getTimeZone(): CarbonTimeZone
    {
        return $this->timeZone;
    }

    /**
     * @param CarbonTimeZone $timeZone
     */
    public function setTimeZone(CarbonTimeZone $timeZone): void
    {
        $this->timeZone = $timeZone;
    }

    public function toCreateArray(): array
    {
        return [
            'discord_id' => $this->getDiscordId(),
            'channel_announce_id' => $this->getChannelAnnounceId(),
            'channel_thread_announce_id' => $this->getChannelThreadAnnounceId(),
        ];
    }

    public function getDiscordId(): string
    {
        return $this->discordId;
    }

    public function setDiscordId(string $discordId): void
    {
        $this->discordId = $discordId;
    }

    public function toUpdateArray(): array
    {
        return [
            'channel_announce_id' => $this->getChannelAnnounceId(),
            'channel_thread_announce_id' => $this->getChannelThreadAnnounceId(),
        ];
    }
}
