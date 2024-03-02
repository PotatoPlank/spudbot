<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023-2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

declare(strict_types=1);

namespace Spudbot\Model;

use Carbon\Carbon;
use Spudbot\Interface\AbstractModel;

class Thread extends AbstractModel
{
    private string $discordId;
    private Guild $guild;
    private Channel $channel;
    private ?string $tag = '';

    public static function hydrateWithArray(array $row): self
    {
        $thread = new self();

        $thread->setId($row['external_id']);
        $thread->setDiscordId($row['discord_id']);
        $thread->setTag($row['tag']);
        $thread->setCreatedAt(Carbon::parse($row['created_at']));
        $thread->setModifiedAt(Carbon::parse($row['updated_at']));

        if ($row['guild']) {
            $thread->setGuild(Guild::hydrateWithArray($row['guild']));
        }
        if ($row['channel']) {
            $thread->setChannel(Channel::hydrateWithArray($row['channel']));
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
