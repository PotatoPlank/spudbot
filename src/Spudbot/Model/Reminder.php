<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023-2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

declare(strict_types=1);

namespace Spudbot\Model;

use Carbon\Carbon;

class Reminder extends AbstractModel
{
    private Guild $guild;
    private Channel $channel;
    private ?string $mentionRole = null;
    private Carbon $scheduledAt;
    private ?string $repeats = null;
    private string $description;

    public function getLocalScheduledAt(): Carbon
    {
        return $this->scheduledAt->copy()->setTimezone($this->getGuild()->getTimeZone());
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

    public function toCreateArray(): array
    {
        return [
            'guild' => $this->getGuild()->getExternalId(),
            'channel' => $this->getChannel()->getExternalId(),
            'description' => $this->getDescription(),
            'mention_role' => $this->getMentionRole(),
            'repeats' => $this->getRepeats(),
            'scheduled_at' => $this->getScheduledAt()->toIso8601String(),
        ];
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

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return string|null
     */
    public function getMentionRole(): ?string
    {
        return $this->mentionRole;
    }

    /**
     * @param string|null $mentionRole
     */
    public function setMentionRole(?string $mentionRole): void
    {
        $this->mentionRole = $mentionRole;
    }

    /**
     * @return string|null
     */
    public function getRepeats(): ?string
    {
        return $this->repeats;
    }

    /**
     * @param string|null $repeats
     */
    public function setRepeats(?string $repeats): void
    {
        $this->repeats = $repeats;
    }

    /**
     * @return Carbon
     */
    public function getScheduledAt(): Carbon
    {
        return $this->scheduledAt->copy();
    }

    /**
     * @param Carbon $scheduledAt
     */
    public function setScheduledAt(Carbon $scheduledAt): void
    {
        $this->scheduledAt = $scheduledAt->copy()->setTimezone('UTC');
    }

    public function toUpdateArray(): array
    {
        return [
            'repeats' => $this->getRepeats(),
            'scheduled_at' => $this->getScheduledAt()->toIso8601String(),
            'mention_role' => $this->getMentionRole(),
            'description' => $this->getDescription(),
        ];
    }
}
