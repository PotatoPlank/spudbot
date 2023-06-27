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

class Reminder extends IModel
{
    private Guild $guild;
    private Channel $channel;
    private ?string $mentionableRole = null;
    private Carbon $scheduledAt;
    private ?string $repeats = null;
    private string $description;


    public static function withDatabaseRow(array $row, ?Guild $guild = null, ?Channel $channel = null): self
    {
        $reminder = new self();

        if (array_key_exists('r_id', $row)) {
            $reminder->setId($row['r_id']);
            $reminder->setDescription($row['r_description']);
            $reminder->setMentionableRole($row['r_mention_role']);
            $reminder->setScheduledAt(Carbon::parse($row['r_scheduled_at']));
            $reminder->setRepeats($row['r_repeats']);
            $reminder->setChannel(Channel::withDatabaseRow($row));
            $reminder->setGuild(Guild::withDatabaseRow($row));
            $reminder->setCreatedAt(Carbon::parse($row['m_created_at']));
            $reminder->setModifiedAt(Carbon::parse($row['m_modified_at']));
        } else {
            if (!isset($guild)) {
                throw new \InvalidArgumentException('Guild is required when you\'re not using joins.');
            }
            if (!isset($channel)) {
                throw new \InvalidArgumentException('Channel is required when you\'re not using joins.');
            }
            $reminder->setId($row['id']);
            $reminder->setDescription($row['description']);
            $reminder->setMentionableRole($row['mention_role']);
            $reminder->setScheduledAt(Carbon::parse($row['scheduled_at']));
            $reminder->setRepeats($row['repeats']);
            $reminder->setChannel($channel);
            $reminder->setGuild($guild);
            $reminder->setCreatedAt(Carbon::parse($row['created_at']));
            $reminder->setModifiedAt(Carbon::parse($row['modified_at']));
        }

        return $reminder;
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
     * @return string|null
     */
    public function getMentionableRole(): ?string
    {
        return $this->mentionableRole;
    }

    /**
     * @param string|null $mentionableRole
     */
    public function setMentionableRole(?string $mentionableRole): void
    {
        $this->mentionableRole = $mentionableRole;
    }

    /**
     * @return Carbon
     */
    public function getScheduledAt(): Carbon
    {
        return $this->scheduledAt;
    }

    /**
     * @param Carbon $scheduledAt
     */
    public function setScheduledAt(Carbon $scheduledAt): void
    {
        $this->scheduledAt = $scheduledAt;
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

}