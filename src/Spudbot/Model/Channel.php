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

class Channel extends IModel
{
    private Guild $guild;
    private string $discordId;

    public static function withDatabaseRow(array $row, ?Guild $guild = null): self
    {
        $channel = new self();

        if (array_key_exists('m_id', $row)) {
            $channel->setId($row['c_id']);
            $channel->setDiscordId($row['c_discord_id']);
            $channel->setGuild(Guild::withDatabaseRow($row));
            $channel->setCreatedAt(Carbon::parse($row['c_created_at']));
            $channel->setModifiedAt(Carbon::parse($row['c_modified_at']));
        } else {
            if (!isset($guild)) {
                throw new \InvalidArgumentException('Guild is required when you\'re not using joins.');
            }
            $channel->setId($row['id']);
            $channel->setDiscordId($row['discord_id']);
            $channel->setGuild($guild);
            $channel->setCreatedAt(Carbon::parse($row['created_at']));
            $channel->setModifiedAt(Carbon::parse($row['modified_at']));
        }

        return $channel;
    }

    /**
     * @return string
     */
    public function getDiscordId(): string
    {
        return $this->discordId;
    }

    /**
     * @param string $discordId
     */
    public function setDiscordId(string $discordId): void
    {
        $this->discordId = $discordId;
    }

    /**
     * @return Guild
     */
    public function getGuild(): Guild
    {
        return $this->guild;
    }

    /**
     * @param Guild $guild
     */
    public function setGuild(Guild $guild): void
    {
        $this->guild = $guild;
    }

}