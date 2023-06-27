<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

declare(strict_types=1);

namespace Spudbot\Model;

use Carbon\Carbon;
use Carbon\CarbonTimeZone;
use Spudbot\Interface\IModel;

class Guild extends IModel
{
    private string $discordId;
    private ?string $outputChannelId;
    private ?string $outputThreadId;
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