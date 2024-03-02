<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023-2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

declare(strict_types=1);

namespace Spudbot\Model;

use Carbon\Carbon;
use Carbon\CarbonTimeZone;
use Discord\Discord;
use Discord\Parts\Channel\Channel;
use Spudbot\Interface\AbstractModel;

class Guild extends AbstractModel
{
    private string $discordId;
    private ?string $outputChannelId = null;
    private ?string $outputThreadId = null;
    private CarbonTimeZone $timeZone;

    public function __construct()
    {
        $this->timeZone = new CarbonTimeZone('America/New_York');
    }

    public static function withDatabaseRow(array $row): self
    {
        $guild = new self();

        if (array_key_exists('g_id', $row)) {
            $guild->setId($row['g_id']);
            $guild->setDiscordId($row['g_discord_id']);
            $guild->setOutputChannelId($row['g_output_channel_id']);
            $guild->setOutputThreadId($row['g_output_thread_id']);
            $guild->setCreatedAt(Carbon::parse($row['g_created_at']));
            $guild->setModifiedAt(Carbon::parse($row['g_modified_at']));
        } else {
            $guild->setId($row['id']);
            $guild->setDiscordId($row['discord_id']);
            $guild->setOutputChannelId($row['output_channel_id']);
            $guild->setOutputThreadId($row['output_thread_id']);
            $guild->setCreatedAt(Carbon::parse($row['created_at']));
            $guild->setModifiedAt(Carbon::parse($row['modified_at']));
        }

        return $guild;
    }

    public static function hydrateWithArray(array $row): self
    {
        $guild = new self();

        $guild->setId($row['external_id']);
        $guild->setDiscordId($row['discord_id']);
        $guild->setOutputChannelId($row['channel_announce_id']);
        $guild->setOutputThreadId($row['channel_thread_announce_id']);
        $guild->setCreatedAt(Carbon::parse($row['created_at']));
        $guild->setModifiedAt(Carbon::parse($row['updated_at']));

        return $guild;
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

    public function getDiscordId(): string
    {
        return $this->discordId;
    }

    public function setDiscordId(string $discordId): void
    {
        $this->discordId = $discordId;
    }

    public function getOutputLocationId(): ?string
    {
        if (!empty($this->getOutputThreadId())) {
            return $this->getOutputThreadId();
        }
        return $this->getOutputChannelId();
    }

    public function getOutputThreadId(): ?string
    {
        return $this->outputThreadId;
    }

    public function setOutputThreadId(?string $threadId): void
    {
        $this->outputThreadId = $threadId;
    }

    public function getOutputChannelId(): ?string
    {
        return $this->outputChannelId;
    }

    public function setOutputChannelId(?string $channelId): void
    {
        $this->outputChannelId = $channelId;
    }

    public function getOutputPart(\Discord\Parts\Guild\Guild $guild): Channel
    {
        $channelId = $this->getOutputChannelId();
        $threadId = $this->getOutputThreadId();

        $outputPart = $guild->channels->get('id', $channelId);
        if (!$outputPart) {
            throw new \BadMethodCallException(
                "Failed locating channel {$channelId} for {$guild->id}."
            );
        }
        if ($this->isOutputLocationThread()) {
            $outputPart = $outputPart->threads->get('id', $threadId);
            if (!$outputPart) {
                throw new \BadMethodCallException(
                    "Failed locating thread {$threadId} in channel {$channelId} for {$guild->id}"
                );
            }
        }
        return $outputPart;
    }

    public function isOutputLocationThread(): bool
    {
        return !empty($this->getOutputThreadId());
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
}
