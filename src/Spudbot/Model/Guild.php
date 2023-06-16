<?php
declare(strict_types=1);

namespace Spudbot\Model;

use Carbon\Carbon;
use Spudbot\Interface\IModel;

class Guild extends IModel
{
    private string $discordId;
    private ?string $outputChannelId;
    private ?string $outputThreadId;

    public static function withDatabaseRow(array $row): self
    {
        $guild = new self();

        $guild->setId($row['g_id'] ?? $row['id']);
        $guild->setDiscordId($row['g_discord_id'] ?? $row['discord_id']);
        $guild->setOutputChannelId($row['g_output_channel_id'] ?? $row['output_channel_id']);
        $guild->setOutputThreadId($row['g_output_thread_id'] ?? $row['output_thread_id']);
        $guild->setCreatedAt(Carbon::parse($row['g_created_at'] ?? $row['created_at']));
        $guild->setModifiedAt(Carbon::parse($row['g_modified_at'] ?? $row['modified_at']));

        return $guild;
    }

    public function setDiscordId(string $discordId): void
    {
        $this->discordId = $discordId;
    }

    public function setOutputChannelId(?string $channelId): void
    {
        $this->outputChannelId = $channelId;
    }
    public function setOutputThreadId(?string $threadId): void
    {
        $this->outputThreadId = $threadId;
    }

    public function getDiscordId(): string
    {
        return $this->discordId;
    }

    public function getOutputChannelId(): ?string
    {
        return $this->outputChannelId;
    }

    public function getOutputThreadId(): ?string
    {
        return $this->outputThreadId;
    }

    public function getOutputLocationId(): ?string
    {
        if(!empty($this->getOutputThreadId()))
        {
            return $this->getOutputThreadId();
        }
        return $this->getOutputChannelId();
    }

    public function isOutputLocationThread(): bool
    {
        return !empty($this->getOutputThreadId());
    }
}