<?php
declare(strict_types=1);

namespace Spudbot\Model;

use Carbon\Carbon;
use Spudbot\Model;

class Event extends Model
{
    private string $guildId;
    private string $channelId;
    private string $name;
    private \Spudbot\Type\Event $type;
    private ?string $seshId;
    private ?string $nativeId;
    private Carbon $scheduledAt;

    public function setGuildId(string $guildId): void
    {
        $this->guildId = $guildId;
    }

    public function getGuildId(): string
    {
        return $this->guildId;
    }


    public function setChannelId(string $channelId): void
    {
        $this->channelId = $channelId;
    }

    public function getChannelId(): string
    {
        return $this->channelId;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setType(\Spudbot\Type\Event $type): void
    {
        $this->type = $type;
    }

    public function getType(): \Spudbot\Type\Event
    {
        return $this->type;
    }

    public function setSeshId(?string $id): void
    {
        $this->seshId = $id;
    }

    public function getSeshId(): ?string
    {
        return $this->seshId;
    }

    public function setNativeId(?string $id): void
    {
        $this->nativeId = $id;
    }

    public function getNativeId(): ?string
    {
        return $this->nativeId;
    }

    public function setScheduledAt(Carbon $scheduledAt): void
    {
        $this->scheduledAt = $scheduledAt;
    }

    public function getScheduledAt(): Carbon
    {
        return $this->scheduledAt;
    }
}