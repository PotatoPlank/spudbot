<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023-2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

declare(strict_types=1);

namespace Spudbot\Model;

use Carbon\Carbon;
use Spudbot\Hydrator\Strategy\CarbonStrategy;
use Spudbot\Hydrator\Strategy\ModelStrategy;
use Spudbot\Hydrator\Strategy\UsesStrategy;

class Reminder extends AbstractModel
{
    #[UsesStrategy(new ModelStrategy(Guild::class))]
    private Guild $guild;
    #[UsesStrategy(new ModelStrategy(Channel::class))]
    private Channel $channel;
    private ?string $mentionRole = null;
    #[UsesStrategy(new CarbonStrategy())]
    private Carbon $scheduledAt;
    private ?string $repeats = null;
    private string $description;

    public function getLocalScheduledAt(): Carbon
    {
        return $this->scheduledAt->copy()->setTimezone($this->getGuild()->timeZone);
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
}
