<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023-2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

declare(strict_types=1);

namespace Spudbot\Model;

class Channel extends AbstractModel
{
    private Guild $guild;
    private string $discordId;

    public function toCreateArray(): array
    {
        return [
            'discord_id' => $this->getDiscordId(),
            'guild_id' => $this->getGuild()->getExternalId(),
        ];
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

    public function toUpdateArray(): array
    {
        return [
            'discord_id' => $this->getDiscordId(),
            'guild_id' => $this->getGuild()->getExternalId(),
        ];
    }
}
