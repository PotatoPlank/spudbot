<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

declare(strict_types=1);

namespace Spudbot\Model;

use Carbon\Carbon;
use Spudbot\Interface\IModel;

class Thread extends IModel
{
    private string $discordId;
    private Guild $guild;
    private Channel $channel;
    private ?string $tag = '';

    public static function withDatabaseRow(array $row, ?Guild $guild = null, ?Channel $channel = null): self
    {
        $thread = new self();

        if (array_key_exists('t_id', $row)) {
            $thread->setId($row['t_id']);
            $thread->setDiscordId($row['t_discord_id']);
            $thread->setGuild(Guild::withDatabaseRow($row));
            $thread->setChannel(Channel::withDatabaseRow($row));
            $thread->setTag($row['t_tag']);
            $thread->setCreatedAt(Carbon::parse($row['t_created_at']));
            $thread->setModifiedAt(Carbon::parse($row['t_modified_at']));
        } else {
            if (!isset($guild)) {
                throw new \InvalidArgumentException('Guild is required when you\'re not using joins.');
            }
            if (!isset($channel)) {
                throw new \InvalidArgumentException('Channel is required when you\'re not using joins.');
            }
            $thread->setId($row['t_id']);
            $thread->setDiscordId($row['t_discord_id']);
            $thread->setGuild($guild);
            $thread->setChannel($channel);
            $thread->setTag($row['t_tag']);
            $thread->setCreatedAt(Carbon::parse($row['t_created_at']));
            $thread->setModifiedAt(Carbon::parse($row['t_modified_at']));
        }

        return $thread;
    }

    /**
     * @return Channel
     */
    public function getChannel(): Channel
    {
        return $this->channel;
    }

    /**
     * @param Channel $channel
     */
    public function setChannel(Channel $channel): void
    {
        $this->channel = $channel;
    }

    public function getTag(): ?string
    {
        return $this->tag;
    }

    public function setTag(?string $tag): void
    {
        $this->tag = $tag;
    }

    public function getDiscordId(): string
    {
        return $this->discordId;
    }

    public function setDiscordId(string $discordId): void
    {
        $this->discordId = $discordId;
    }

    public function getGuild(): Guild
    {
        return $this->guild;
    }

    public function setGuild(Guild $guild): void
    {
        $this->guild = $guild;
    }
}