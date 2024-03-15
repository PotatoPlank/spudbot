<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023-2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

declare(strict_types=1);

namespace Spudbot\Model;

class Thread extends AbstractModel
{
    private string $discordId;
    private Guild $guild;
    private Channel $channel;
    private ?string $tag = '';

    public function toCreateArray(): array
    {
        return [
            'discord_id' => $this->getDiscordId(),
            'guild' => $this->getGuild()->getExternalId(),
            'channel' => $this->getChannel()->getExternalId(),
            'tag' => $this->getTag(),
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

    public function getGuild(): Guild
    {
        return $this->guild;
    }

    public function setGuild(Guild $guild): void
    {
        $this->guild = $guild;
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

    public function toUpdateArray(): array
    {
        return [
            'tag' => $this->getTag(),
        ];
    }
}
